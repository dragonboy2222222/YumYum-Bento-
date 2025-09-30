// import React, { useState, useEffect, useRef } from 'react';

// // IMPORTANT: Update this URL to match your PHP backend's location
// const CHATBOT_API_URL = 'http://localhost/Webpage/api/chatbot_handler.php'; 

// const Chatbot = () => {
//     const [isOpen, setIsOpen] = useState(false);
//     const [messages, setMessages] = useState([
//         { text: "Hello! How can I help you today?", sender: 'bot' }
//     ]);
//     const [input, setInput] = useState('');
//     const [predefinedQuestions] = useState([
//         "How do your subscriptions work?",
//         "What are your delivery areas?",
//         "Can I customize my meals?",
//         "What is the Classic Lunchbox?",
//         "How do I cancel my subscription?",
//         "What was my last order?", // Added a personalized question
//     ]);
//     const messagesEndRef = useRef(null);

//     // Effect to scroll to the bottom of the chat messages
//     useEffect(() => {
//         if (messagesEndRef.current) {
//             messagesEndRef.current.scrollIntoView({ behavior: "smooth" });
//         }
//     }, [messages, isOpen]); 

//     const toggleChat = () => {
//         setIsOpen(!isOpen);
//     };

//     const handlePredefinedQuestionClick = (question) => {
//         setInput(question);
//         sendMessage(question);
//     };

//     const handleInputChange = (e) => {
//         setInput(e.target.value);
//     };

//     const sendMessage = async (messageText) => {
//         if (!messageText.trim()) return;

//         // 1. Add user message to state
//         const userMessage = { text: messageText, sender: 'user' };
//         setMessages(prevMessages => [...prevMessages, userMessage]);
//         setInput('');

//         try {
//             // 2. Simulate typing and prepare the request
//             const typingMessage = { text: "Typing...", sender: 'bot' };
//             setMessages(prevMessages => [...prevMessages, typingMessage]);
            
//             // --- Crucial Update for Session Management ---
//             const response = await fetch(CHATBOT_API_URL, {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json',
//                 },
//                 // Include session cookies for personalization
//                 credentials: 'include', 
//                 body: JSON.stringify({ message: messageText }),
//             });

//             if (!response.ok) {
//                 throw new Error('Chatbot API request failed with status: ' + response.status);
//             }

//             const data = await response.json();
//             const botResponseText = data.reply || "Sorry, I'm having trouble connecting right now. Please try again later.";

//             // 3. Replace 'Typing...' message and add bot's response
//             setMessages(prevMessages => {
//                 // Ensure 'Typing...' is the last message before attempting to remove it
//                 const lastMessage = prevMessages[prevMessages.length - 1];
//                 const updatedMessages = (lastMessage && lastMessage.text === "Typing...") 
//                     ? prevMessages.slice(0, -1) // Remove the last 'Typing...' message
//                     : prevMessages; // Safety fallback

//                 updatedMessages.push({ text: botResponseText, sender: 'bot' });
//                 return updatedMessages;
//             });

//         } catch (error) {
//             console.error("Chatbot error:", error);
//             // 4. Handle error by replacing 'Typing...' with an error message
//             setMessages(prevMessages => {
//                 const lastMessage = prevMessages[prevMessages.length - 1];
//                 const updatedMessages = (lastMessage && lastMessage.text === "Typing...") 
//                     ? prevMessages.slice(0, -1) 
//                     : prevMessages; 

//                 updatedMessages.push({ text: "Error: Could not get a response. (Check console for details)", sender: 'bot' });
//                 return updatedMessages;
//             });
//         }
//     };

//     const handleSubmit = (e) => {
//         e.preventDefault();
//         sendMessage(input);
//     };

//     // (Your renderChatButton and renderChatBox JSX functions remain the same)
//     const renderChatButton = () => (
//         <button
//             id="chat-toggle"
//             className="btn btn-danger rounded-circle position-fixed"
//             onClick={toggleChat}
//             style={{ bottom: '20px', right: '20px', width: '60px', height: '60px', zIndex: 999 }}
//         >
//             {isOpen ? '❌' : '💬'}
//         </button>
//     );

//     const renderChatBox = () => (
//         <div
//             id="chat-box"
//             className={`card shadow position-fixed ${isOpen ? '' : 'd-none'}`}
//             style={{ bottom: '90px', right: '20px', width: '300px', maxHeight: '400px', zIndex: 999 }}
//         >
//             <div className="card-header bg-danger text-white">Chat with us</div>
            
//             <div id="chat-messages" className="card-body overflow-auto" style={{ height: '250px' }}>
//                 {messages.map((msg, index) => (
//                     <div key={index} className={`d-flex ${msg.sender === 'user' ? 'justify-content-end' : 'justify-content-start'} mb-2`}>
//                         <span 
//                             className={`badge ${msg.sender === 'user' ? 'bg-primary text-white' : 'bg-light text-dark border'}`}
//                             style={{maxWidth: '80%', whiteSpace: 'normal'}}
//                         >
//                             {msg.text}
//                         </span>
//                     </div>
//                 ))}
//                 <div ref={messagesEndRef} />
//             </div>
            
//             <div id="predefined-questions" className="card-body overflow-auto predefined-questions-container">
//                 {predefinedQuestions.map((q, index) => (
//                     <button
//                         key={index}
//                         className="btn btn-outline-secondary btn-sm predefined-q"
//                         onClick={() => handlePredefinedQuestionClick(q)}
//                     >
//                         {q}
//                     </button>
//                 ))}
//             </div>

//             <form id="chat-form" className="card-footer d-flex" onSubmit={handleSubmit}>
//                 <input
//                     type="text"
//                     id="chat-input"
//                     className="form-control me-2"
//                     placeholder="Type a message..."
//                     value={input}
//                     onChange={handleInputChange}
//                     disabled={messages.length > 0 && messages[messages.length - 1].text === 'Typing...'}
//                 />
//                 <button 
//                     className="btn btn-danger" 
//                     type="submit"
//                     disabled={!input.trim() || (messages.length > 0 && messages[messages.length - 1].text === 'Typing...')}
//                 >
//                     Send
//                 </button>
//             </form>
//         </div>
//     );

//     return (
//         <>
//             {renderChatButton()}
//             {renderChatBox()}
//         </>
//     );
// };

// export default Chatbot;