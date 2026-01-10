import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Send, MessageSquare } from 'lucide-react';
import { Layout } from '@/components/layout';
import { Card, Button, Spinner } from '@/components/ui';
import { messagesApi } from '@/api';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { clsx } from 'clsx';

export function MessagesPage() {
  const [selectedConversation, setSelectedConversation] = useState<string | null>(null);
  const [newMessage, setNewMessage] = useState('');
  const queryClient = useQueryClient();

  // Fetch conversations
  const { data: conversations, isLoading: loadingConversations } = useQuery({
    queryKey: ['conversations'],
    queryFn: () => messagesApi.getConversations(),
  });

  // Fetch messages for selected conversation
  const { data: messages, isLoading: loadingMessages } = useQuery({
    queryKey: ['messages', selectedConversation],
    queryFn: () => messagesApi.getConversation(selectedConversation!),
    enabled: !!selectedConversation,
  });

  // Send message mutation
  const sendMessageMutation = useMutation({
    mutationFn: (content: string) =>
      messagesApi.send({
        content,
        conversationId: selectedConversation || undefined,
      }),
    onSuccess: () => {
      setNewMessage('');
      queryClient.invalidateQueries({ queryKey: ['messages', selectedConversation] });
      queryClient.invalidateQueries({ queryKey: ['conversations'] });
    },
  });

  const handleSend = (e: React.FormEvent) => {
    e.preventDefault();
    if (newMessage.trim()) {
      sendMessageMutation.mutate(newMessage.trim());
    }
  };

  return (
    <Layout>
      <div className="bg-gray-50 min-h-screen">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <h1 className="text-3xl font-black text-gray-900 mb-8">Messages</h1>

          <div className="grid lg:grid-cols-3 gap-6 h-[calc(100vh-250px)]">
            {/* Conversations list */}
            <Card variant="elevated" padding="none" className="overflow-hidden">
              <div className="p-4 border-b border-gray-100">
                <h2 className="font-bold text-gray-900">Conversations</h2>
              </div>

              {loadingConversations ? (
                <div className="flex justify-center py-8">
                  <Spinner />
                </div>
              ) : conversations?.length === 0 ? (
                <div className="p-6 text-center text-gray-500">
                  <MessageSquare className="w-10 h-10 mx-auto mb-2 text-gray-300" />
                  <p>Aucune conversation</p>
                </div>
              ) : (
                <div className="overflow-y-auto max-h-[calc(100%-60px)]">
                  {conversations?.map((conv) => (
                    <button
                      key={conv.id}
                      onClick={() => setSelectedConversation(conv.id)}
                      className={clsx(
                        'w-full p-4 text-left border-b border-gray-100 transition-colors',
                        selectedConversation === conv.id
                          ? 'bg-[#FF6B00]/5 border-l-4 border-l-[#FF6B00]'
                          : 'hover:bg-gray-50'
                      )}
                    >
                      <div className="flex justify-between items-start mb-1">
                        <span className="font-medium text-gray-900">
                          Support Quernel Auto
                        </span>
                        {conv.unreadCount > 0 && (
                          <span className="px-2 py-0.5 text-xs font-bold bg-[#FF6B00] text-white rounded-full">
                            {conv.unreadCount}
                          </span>
                        )}
                      </div>
                      {conv.lastMessage && (
                        <p className="text-sm text-gray-500 truncate">
                          {conv.lastMessage.content}
                        </p>
                      )}
                    </button>
                  ))}
                </div>
              )}

              {/* New conversation button */}
              <div className="p-4 border-t border-gray-100">
                <Button
                  fullWidth
                  variant="outline"
                  onClick={() => setSelectedConversation('new')}
                >
                  Nouvelle conversation
                </Button>
              </div>
            </Card>

            {/* Messages area */}
            <Card variant="elevated" padding="none" className="lg:col-span-2 flex flex-col overflow-hidden">
              {!selectedConversation ? (
                <div className="flex-1 flex items-center justify-center text-gray-500">
                  <div className="text-center">
                    <MessageSquare className="w-16 h-16 mx-auto mb-4 text-gray-200" />
                    <p>Sélectionnez une conversation</p>
                  </div>
                </div>
              ) : (
                <>
                  {/* Header */}
                  <div className="p-4 border-b border-gray-100">
                    <h3 className="font-bold text-gray-900">Support Quernel Auto</h3>
                    <p className="text-sm text-gray-500">Nous répondons généralement sous 24h</p>
                  </div>

                  {/* Messages */}
                  <div className="flex-1 overflow-y-auto p-4 space-y-4">
                    {loadingMessages ? (
                      <div className="flex justify-center py-8">
                        <Spinner />
                      </div>
                    ) : messages?.length === 0 ? (
                      <div className="text-center text-gray-500 py-8">
                        <p>Commencez la conversation</p>
                      </div>
                    ) : (
                      messages?.map((message) => (
                        <div
                          key={message.id}
                          className={clsx(
                            'flex',
                            message.isFromUser ? 'justify-end' : 'justify-start'
                          )}
                        >
                          <div
                            className={clsx(
                              'max-w-[70%] rounded-2xl px-4 py-3',
                              message.isFromUser
                                ? 'bg-[#FF6B00] text-white rounded-br-md'
                                : 'bg-gray-100 text-gray-900 rounded-bl-md'
                            )}
                          >
                            <p className="whitespace-pre-wrap">{message.content}</p>
                            <p
                              className={clsx(
                                'text-xs mt-1',
                                message.isFromUser ? 'text-white/70' : 'text-gray-500'
                              )}
                            >
                              {format(new Date(message.createdAt), 'HH:mm', { locale: fr })}
                            </p>
                          </div>
                        </div>
                      ))
                    )}
                  </div>

                  {/* Input */}
                  <form onSubmit={handleSend} className="p-4 border-t border-gray-100">
                    <div className="flex gap-3">
                      <input
                        type="text"
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        placeholder="Écrivez votre message..."
                        className="flex-1 px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-[#FF6B00] focus:ring-4 focus:ring-[#FF6B00]/10 outline-none transition-all"
                      />
                      <Button
                        type="submit"
                        disabled={!newMessage.trim()}
                        isLoading={sendMessageMutation.isPending}
                        leftIcon={<Send className="w-5 h-5" />}
                      >
                        Envoyer
                      </Button>
                    </div>
                  </form>
                </>
              )}
            </Card>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default MessagesPage;
