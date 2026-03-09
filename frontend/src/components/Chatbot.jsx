import React, { useState, useEffect, useRef } from 'react';
import { MessageCircle, X, Send, User, Bot, Minimize2 } from 'lucide-react';
import { sendChatMessage } from '../api/apiService';

const Chatbot = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [isMinimized, setIsMinimized] = useState(false);
    const [sessionId, setSessionId] = useState(null);
    const [messages, setMessages] = useState([
        {
            text: "Hi! I'm your Khushi Home Sofa Repair assistant. How can I help you today?",
            sender: 'bot',
            timestamp: new Date()
        }
    ]);
    const [inputMessage, setInputMessage] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const messagesEndRef = useRef(null);

    // Load session from localStorage on mount
    useEffect(() => {
        const savedSessionId = localStorage.getItem('chatSessionId');
        if (savedSessionId) {
            setSessionId(savedSessionId);
        }
    }, []);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSendMessage = async () => {
        if (!inputMessage.trim()) return;

        const userMessage = {
            text: inputMessage,
            sender: 'user',
            timestamp: new Date()
        };

        setMessages(prev => [...prev, userMessage]);
        setInputMessage('');
        setIsTyping(true);

        try {
            const response = await sendChatMessage(inputMessage, sessionId);

            // Save session ID
            if (response.data.sessionId && !sessionId) {
                setSessionId(response.data.sessionId);
                localStorage.setItem('chatSessionId', response.data.sessionId);
            }

            const botMessage = {
                text: response.data.response,
                sender: 'bot',
                timestamp: new Date(),
                suggestions: response.data.suggestions,
                intent: response.data.intent,
                confidence: response.data.confidence
            };

            setTimeout(() => {
                setMessages(prev => [...prev, botMessage]);
                setIsTyping(false);
            }, 500);

        } catch (error) {
            console.error('Chatbot error:', error);
            const errorMessage = {
                text: "I'm sorry, I'm having trouble responding right now. Please try again or contact us at +919689861811.",
                sender: 'bot',
                timestamp: new Date()
            };
            setMessages(prev => [...prev, errorMessage]);
            setIsTyping(false);
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage();
        }
    };

    const handleSuggestionClick = (suggestion) => {
        setInputMessage(suggestion);
    };

    const formatTime = (date) => {
        return new Date(date).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    if (!isOpen) {
        return (
            <button
                onClick={() => setIsOpen(true)}
                className="fixed bottom-6 right-6 bg-orange-600 text-white p-4 rounded-full shadow-lg hover:bg-orange-700 transition-all transform hover:scale-110 z-50"
                aria-label="Open chat"
            >
                <MessageCircle className="w-6 h-6" />
            </button>
        );
    }

    return (
        <div className={`fixed bottom-6 right-6 z-50 transition-all duration-300 ${isMinimized ? 'w-80' : 'w-96'}`}>
            <div className="bg-white rounded-lg shadow-2xl flex flex-col" style={{ height: isMinimized ? '60px' : '600px' }}>
                {/* Header */}
                <div className="bg-gradient-to-r from-orange-600 to-orange-500 text-white p-4 rounded-t-lg flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <Bot className="w-6 h-6" />
                        <div>
                            <h3 className="font-bold">Khushi Support</h3>
                            <p className="text-xs opacity-90">Always here to help</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => setIsMinimized(!isMinimized)}
                            className="hover:bg-orange-700 p-1 rounded transition-colors"
                            aria-label={isMinimized ? "Maximize" : "Minimize"}
                        >
                            <Minimize2 className="w-5 h-5" />
                        </button>
                        <button
                            onClick={() => setIsOpen(false)}
                            className="hover:bg-orange-700 p-1 rounded transition-colors"
                            aria-label="Close chat"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Messages Container */}
                {!isMinimized && (
                    <>
                        <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                            {messages.map((message, index) => (
                                <div key={index}>
                                    <div className={`flex items-start gap-2 ${message.sender === 'user' ? 'flex-row-reverse' : ''}`}>
                                        <div className={`p-2 rounded-full ${message.sender === 'user' ? 'bg-orange-100' : 'bg-blue-100'}`}>
                                            {message.sender === 'user' ? (
                                                <User className="w-4 h-4 text-orange-600" />
                                            ) : (
                                                <Bot className="w-4 h-4 text-blue-600" />
                                            )}
                                        </div>
                                        <div className={`flex flex-col ${message.sender === 'user' ? 'items-end' : 'items-start'} max-w-[75%]`}>
                                            <div className={`p-3 rounded-lg ${
                                                message.sender === 'user' 
                                                    ? 'bg-orange-600 text-white' 
                                                    : 'bg-white border border-gray-200'
                                            }`}>
                                                <p className="text-sm whitespace-pre-wrap">{message.text}</p>
                                            </div>
                                            <span className="text-xs text-gray-500 mt-1">
                                                {formatTime(message.timestamp)}
                                            </span>
                                            {message.suggestions && message.suggestions.length > 0 && (
                                                <div className="flex flex-wrap gap-2 mt-2">
                                                    {message.suggestions.map((suggestion, idx) => (
                                                        <button
                                                            key={idx}
                                                            onClick={() => handleSuggestionClick(suggestion)}
                                                            className="text-xs bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-1 rounded-full transition-colors"
                                                        >
                                                            {suggestion}
                                                        </button>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                            
                            {isTyping && (
                                <div className="flex items-start gap-2">
                                    <div className="p-2 rounded-full bg-blue-100">
                                        <Bot className="w-4 h-4 text-blue-600" />
                                    </div>
                                    <div className="bg-white border border-gray-200 p-3 rounded-lg">
                                        <div className="flex gap-1">
                                            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></div>
                                            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></div>
                                            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></div>
                                        </div>
                                    </div>
                                </div>
                            )}
                            
                            <div ref={messagesEndRef} />
                        </div>

                        {/* Input Area */}
                        <div className="p-4 border-t border-gray-200 bg-white rounded-b-lg">
                            <div className="flex gap-2">
                                <input
                                    type="text"
                                    value={inputMessage}
                                    onChange={(e) => setInputMessage(e.target.value)}
                                    onKeyPress={handleKeyPress}
                                    placeholder="Type your message..."
                                    className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"
                                />
                                <button
                                    onClick={handleSendMessage}
                                    disabled={!inputMessage.trim()}
                                    className="bg-orange-600 text-white p-2 rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    aria-label="Send message"
                                >
                                    <Send className="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default Chatbot;
