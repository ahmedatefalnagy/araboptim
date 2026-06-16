import { useState, useRef, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import axios from 'axios';

export default function Chat({ auth, initialMessages, availableModels }) {
    const [messages, setMessages] = useState(initialMessages);
    const [isLoading, setIsLoading] = useState(false);
    const [input, setInput] = useState('');
    const [selectedModel, setSelectedModel] = useState(availableModels[0]?.name || '');
    const scrollRef = useRef();

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    }, [messages]);

    const handleSend = async (e) => {
        e.preventDefault();
        if (!input.trim() || isLoading) return;

        const userMessage = { role: 'user', content: input };
        const newMessages = [...messages, userMessage];
        setMessages(newMessages);
        setInput('');
        setIsLoading(true);

        try {
            const response = await axios.post(route('ai.chat.process'), {
                messages: newMessages,
                model: selectedModel
            });

            setMessages([...newMessages, response.data.message]);
        } catch (error) {
            setMessages([...newMessages, { 
                role: 'assistant', 
                content: 'عذراً، استغرقت العملية وقتاً طويلاً أو حدث خطأ. يرجى التأكد من أن Ollama يعمل واختيار موديل أصغر إذا استمرت المشكلة.' 
            }]);
        } finally {
            setIsLoading(false);
        }
    };

    const fmtSize = (bytes) => {
        if (!bytes) return '';
        const gb = bytes / (1024 * 1024 * 1024);
        return gb.toFixed(1) + ' GB';
    };

    return (
        <AuthenticatedLayout 
            user={auth.user} 
            header={<h2 className="font-bold text-xl text-gray-800">المساعد الذكي (AI)</h2>}
        >
            <Head title="الدردشة الذكية" />

            <div className="flex flex-col h-[calc(100vh-10rem)] max-w-5xl mx-auto px-4 py-4" dir="rtl">
                
                {/* Chat Container */}
                <div className="flex-1 bg-white rounded-2xl border border-slate-200 shadow-xl flex flex-col overflow-hidden relative">
                    
                    {/* Header */}
                    <div className="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-200">
                                <svg className="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h3 className="text-sm font-black text-slate-800">الدردشة الذكية</h3>
                                <div className="flex items-center gap-3 mt-1">
                                    <div className="flex items-center gap-1.5">
                                        <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Ollama Active</p>
                                    </div>
                                    <div className="h-3 w-px bg-slate-200"></div>
                                    <select 
                                        value={selectedModel} 
                                        onChange={e => setSelectedModel(e.target.value)}
                                        className="bg-transparent border-none p-0 text-[10px] font-black text-blue-600 focus:ring-0 cursor-pointer hover:text-blue-800 transition-colors uppercase tracking-widest"
                                    >
                                        {availableModels.filter(m => !m.name.includes('embed')).map(m => (
                                            <option key={m.name} value={m.name} className="text-slate-800 font-bold">
                                                {m.name} ({fmtSize(m.size)})
                                            </option>
                                        ))}
                                    </select>
                                    <button 
                                        onClick={() => window.location.reload()} 
                                        className="p-1 hover:bg-slate-200 rounded-md transition-colors text-slate-400 hover:text-slate-600"
                                        title="تحديث قائمة الموديلات"
                                    >
                                        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            {availableModels.length === 0 && (
                                <div className="text-[10px] font-bold text-red-500 animate-bounce">
                                    ⚠️ يرجى تحميل موديل (Pull) عبر Ollama
                                </div>
                            )}
                            <button onClick={() => setMessages(initialMessages)} className="text-[11px] font-bold text-slate-400 hover:text-red-500 transition-colors uppercase tracking-wider flex items-center gap-1.5">
                                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                مسح المحادثة
                            </button>
                        </div>
                    </div>

                    {/* Messages Area */}
                    <div 
                        ref={scrollRef}
                        className="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px] bg-fixed"
                    >
                        {messages.map((msg, i) => (
                            <div key={i} className={`flex ${msg.role === 'user' ? 'justify-start' : 'justify-end'} animate-in fade-in slide-in-from-bottom-2 duration-300`}>
                                <div className={`max-w-[85%] flex flex-col ${msg.role === 'user' ? 'items-start' : 'items-end'}`}>
                                    <div className={`px-5 py-3.5 rounded-2xl shadow-sm ${
                                        msg.role === 'user' 
                                            ? 'bg-slate-900 text-white rounded-tr-none' 
                                            : 'bg-white border border-slate-200 text-slate-800 rounded-tl-none'
                                    }`}>
                                        <p className="text-sm leading-relaxed whitespace-pre-wrap font-medium">
                                            {msg.content}
                                        </p>
                                    </div>
                                    <span className="mt-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">
                                        {msg.role === 'user' ? 'أنت' : 'المساعد الذكي'}
                                    </span>
                                </div>
                            </div>
                        ))}
                        {isLoading && (
                            <div className="flex justify-end">
                                <div className="bg-white border border-slate-200 px-6 py-4 rounded-2xl rounded-tl-none shadow-sm flex items-center gap-3">
                                    <div className="flex gap-1">
                                        <span className="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                                        <span className="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                                        <span className="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span>
                                    </div>
                                    <span className="text-[11px] font-bold text-slate-400 uppercase tracking-widest">جاري التفكير عبر {selectedModel}...</span>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Input Area */}
                    <div className="p-4 border-t border-slate-100 bg-white shadow-[0_-4px_15px_rgba(0,0,0,0.02)]">
                        <form onSubmit={handleSend} className="relative flex items-center gap-3">
                            <textarea
                                value={input}
                                onChange={e => setInput(e.target.value)}
                                onKeyDown={e => {
                                    if (e.key === 'Enter' && !e.shiftKey) {
                                        e.preventDefault();
                                        handleSend(e);
                                    }
                                }}
                                placeholder="اكتب سؤالك هنا..."
                                className="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-slate-200 focus:border-slate-400 resize-none max-h-32 transition-all font-medium pr-12"
                                rows={1}
                            />
                            <button 
                                type="submit"
                                disabled={!input.trim() || isLoading}
                                className={`p-2.5 rounded-xl transition-all shadow-md active:scale-95 ${
                                    !input.trim() || isLoading 
                                        ? 'bg-slate-100 text-slate-300' 
                                        : 'bg-slate-900 text-white hover:bg-slate-800 shadow-slate-200'
                                }`}
                            >
                                <svg className="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            </button>
                        </form>
                        <div className="flex items-center justify-center gap-4 mt-3">
                             <p className="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Ollama Model: {selectedModel}</p>
                             <div className="h-1 w-1 bg-slate-200 rounded-full"></div>
                             <p className="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Timeout: 300s</p>
                        </div>
                    </div>

                </div>

            </div>
        </AuthenticatedLayout>
    );
}
