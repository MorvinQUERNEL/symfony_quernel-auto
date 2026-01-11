import { useState, useRef, useEffect } from 'react';
import { MessageCircle, X, Send, Bot, User } from 'lucide-react';
import { clsx } from 'clsx';
import { findBestResponse, welcomeMessages, quickQuestions as faqQuickQuestions, chatbotTexts, type ChatMessage } from './faqData';
import { useLanguage } from '@/i18n';

export function ChatBot() {
  const { language } = useLanguage();
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [inputValue, setInputValue] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  // Initialize welcome message based on language
  useEffect(() => {
    setMessages([
      {
        id: '1',
        type: 'bot',
        content: welcomeMessages[language],
        timestamp: new Date(),
      },
    ]);
  }, [language]);

  const texts = chatbotTexts[language];
  const quickQuestions = faqQuickQuestions[language];

  // Scroll to bottom when messages change
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  // Focus input when chat opens
  useEffect(() => {
    if (isOpen) {
      setTimeout(() => inputRef.current?.focus(), 100);
    }
  }, [isOpen]);

  const handleSend = () => {
    if (!inputValue.trim()) return;

    const userMessage: ChatMessage = {
      id: Date.now().toString(),
      type: 'user',
      content: inputValue.trim(),
      timestamp: new Date(),
    };

    setMessages((prev) => [...prev, userMessage]);
    setInputValue('');
    setIsTyping(true);

    // Simulate typing delay
    setTimeout(() => {
      const response = findBestResponse(userMessage.content, language);
      const botMessage: ChatMessage = {
        id: (Date.now() + 1).toString(),
        type: 'bot',
        content: response,
        timestamp: new Date(),
      };
      setMessages((prev) => [...prev, botMessage]);
      setIsTyping(false);
    }, 500 + Math.random() * 500);
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const handleQuickQuestion = (question: string) => {
    setInputValue(question);
    setTimeout(() => handleSend(), 100);
  };

  return (
    <>
      {/* Chat bubble button */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className={clsx(
          'fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full shadow-lg transition-all duration-300',
          'flex items-center justify-center',
          'bg-gradient-to-br from-[#FF6B00] to-[#FF8533] hover:from-[#FF8533] hover:to-[#FFAA00]',
          'hover:scale-110 active:scale-95',
          isOpen && 'rotate-90'
        )}
        aria-label={isOpen ? texts.close : texts.open}
      >
        {isOpen ? (
          <X className="w-6 h-6 text-white" />
        ) : (
          <MessageCircle className="w-6 h-6 text-white" />
        )}
      </button>

      {/* Chat window */}
      {isOpen && (
        <div
          className={clsx(
            'fixed bottom-24 right-6 z-50 w-[360px] max-w-[calc(100vw-48px)]',
            'bg-white rounded-2xl shadow-2xl border border-gray-100',
            'flex flex-col overflow-hidden',
            'animate-in fade-in slide-in-from-bottom-4 duration-300'
          )}
          style={{ height: 'min(500px, calc(100vh - 150px))' }}
        >
          {/* Header */}
          <div className="bg-gradient-to-r from-[#FF6B00] to-[#FF8533] px-4 py-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                <Bot className="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 className="text-white font-bold">{texts.title}</h3>
                <p className="text-white/80 text-xs">{texts.online}</p>
              </div>
            </div>
          </div>

          {/* Messages */}
          <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            {messages.map((message) => (
              <div
                key={message.id}
                className={clsx(
                  'flex gap-2',
                  message.type === 'user' ? 'justify-end' : 'justify-start'
                )}
              >
                {message.type === 'bot' && (
                  <div className="w-8 h-8 rounded-full bg-[#FF6B00]/10 flex items-center justify-center flex-shrink-0">
                    <Bot className="w-4 h-4 text-[#FF6B00]" />
                  </div>
                )}
                <div
                  className={clsx(
                    'max-w-[80%] rounded-2xl px-4 py-2.5',
                    message.type === 'user'
                      ? 'bg-[#FF6B00] text-white rounded-br-md'
                      : 'bg-white text-gray-800 shadow-sm border border-gray-100 rounded-bl-md'
                  )}
                >
                  <p className="text-sm whitespace-pre-wrap">{message.content}</p>
                </div>
                {message.type === 'user' && (
                  <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <User className="w-4 h-4 text-gray-600" />
                  </div>
                )}
              </div>
            ))}

            {/* Typing indicator */}
            {isTyping && (
              <div className="flex gap-2 justify-start">
                <div className="w-8 h-8 rounded-full bg-[#FF6B00]/10 flex items-center justify-center flex-shrink-0">
                  <Bot className="w-4 h-4 text-[#FF6B00]" />
                </div>
                <div className="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm border border-gray-100">
                  <div className="flex gap-1">
                    <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                    <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                    <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                  </div>
                </div>
              </div>
            )}

            <div ref={messagesEndRef} />
          </div>

          {/* Quick questions (only show at start) */}
          {messages.length <= 1 && (
            <div className="px-4 py-3 border-t border-gray-100 bg-white">
              <p className="text-xs text-gray-500 mb-2">{texts.frequentQuestions}</p>
              <div className="flex flex-wrap gap-2">
                {quickQuestions.map((question) => (
                  <button
                    key={question}
                    onClick={() => handleQuickQuestion(question)}
                    className="text-xs px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 hover:bg-[#FF6B00]/10 hover:text-[#FF6B00] transition-colors"
                  >
                    {question}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Input */}
          <div className="p-4 border-t border-gray-100 bg-white">
            <div className="flex gap-2">
              <input
                ref={inputRef}
                type="text"
                value={inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder={texts.placeholder}
                className="flex-1 px-4 py-2.5 rounded-xl border-2 border-gray-200 focus:border-[#FF6B00] focus:ring-2 focus:ring-[#FF6B00]/20 outline-none transition-all text-sm"
              />
              <button
                onClick={handleSend}
                disabled={!inputValue.trim()}
                className={clsx(
                  'p-2.5 rounded-xl transition-all',
                  inputValue.trim()
                    ? 'bg-[#FF6B00] text-white hover:bg-[#CC5500]'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                )}
              >
                <Send className="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export default ChatBot;
