import { get, post } from './client';
import type { Message, Conversation, SendMessageData } from '@/types';

export const messagesApi = {
  getConversations: () =>
    get<Conversation[]>('/messages/conversations'),

  getConversation: (conversationId: string) =>
    get<Message[]>(`/messages/conversation/${conversationId}`),

  send: (data: SendMessageData) =>
    post<Message>('/messages/send', data),

  getUnreadCount: () =>
    get<{ count: number }>('/messages/unread-count'),

  markAsRead: (conversationId: string) =>
    post<{ message: string }>(`/messages/conversation/${conversationId}/read`),
};

export default messagesApi;
