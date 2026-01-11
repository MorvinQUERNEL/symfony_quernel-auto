import { get, post } from './client';
import type { Message, Conversation, SendMessageData } from '@/types';

interface ConversationResponse {
  conversationId: string;
  messages: Array<{
    id: number;
    content: string;
    sendAt: string;
    isFromUser: boolean;
    isRead: boolean;
    sender?: {
      id: number;
      firstname: string;
      lastname: string;
    };
  }>;
}

interface ApiConversation {
  conversationId: string;
  lastMessage?: {
    content: string;
    sendAt: string;
    isFromUser: boolean;
  };
  unreadCount: number;
  messageCount: number;
}

export const messagesApi = {
  getConversations: async (): Promise<Conversation[]> => {
    const data = await get<ApiConversation[]>('/messages');
    return data.map((conv) => ({
      id: conv.conversationId,
      lastMessage: conv.lastMessage ? {
        id: 0,
        content: conv.lastMessage.content,
        conversationId: conv.conversationId,
        isFromUser: conv.lastMessage.isFromUser,
        createdAt: conv.lastMessage.sendAt,
        isRead: true,
      } : undefined,
      unreadCount: conv.unreadCount,
    }));
  },

  getConversation: async (conversationId: string): Promise<Message[]> => {
    const data = await get<ConversationResponse>(`/messages/${conversationId}`);
    return data.messages.map((msg) => ({
      id: msg.id,
      content: msg.content,
      conversationId: data.conversationId,
      isFromUser: msg.isFromUser,
      createdAt: msg.sendAt,
      isRead: msg.isRead,
    }));
  },

  send: (data: SendMessageData) =>
    post<Message>('/messages', data),

  getUnreadCount: () =>
    get<{ unreadCount: number }>('/messages/unread-count'),

  markAsRead: (conversationId: string) =>
    post<{ message: string }>(`/messages/${conversationId}/read`),
};

export default messagesApi;
