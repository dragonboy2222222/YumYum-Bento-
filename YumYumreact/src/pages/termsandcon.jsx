// import React, { useEffect, useState } from 'react';
// import Navbar from '../components/Navbar';
// import Footer from '../components/Footer';
// import { fetchHomeData } from '../services/api';

// const Terms = () => {
//   const [navData, setNavData] = useState({ lunchboxes: [], cartCount: 0 });
//   const [loading, setLoading] = useState(true);
//   const [chatOpen, setChatOpen] = useState(false);
//   const [messages, setMessages] = useState([]);
//   const [input, setInput] = useState('');

//   // Fetch nav data
//   useEffect(() => {
//     const loadNavData = async () => {
//       try {
//         const data = await fetchHomeData();
//         setNavData({
//           lunchboxes: data.lunchboxes || [],
//           cartCount: data.cartCount || 0,
//         });
//       } catch (err) {
//         console.error('Error loading nav data:', err);
//       } finally {
//         setLoading(false);
//       }
//     };
//     loadNavData();
//   }, []);

//   const toggleChat = () => setChatOpen(prev => !prev);

//   const handleSendMessage = (e) => {
//     e.preventDefault();
//     if (!input.trim()) return;

//     setMessages(prev => [...prev, { text: input, sender: 'user' }]);
//     setInput('');

//     setTimeout(() => {
//       setMessages(prev => [
//         ...prev,
//         { text: 'Thank you! Our support will reach you soon.', sender: 'bot' }
//       ]);
//     }, 1000);
//   };

//   if (loading) return <p>Loading...</p>;

//   const sections = [
//     {
//       title: '1. Acceptance of Terms',
//       content: 'By accessing or using the services provided by YumYum Bento, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use our services.'
//     },
//     {
//       title: '2. Subscription and Payments',
//       list: [
//         '2.1. Subscription Plans: We offer various subscription plans for our meal delivery service. By subscribing, you agree to pay the recurring fees associated with your chosen plan.',
//         '2.2. No Cancellations or Refunds: Once a payment is made, the order cannot be cancelled and no refunds will be provided.',
//         '2.3. Payment Information: You agree to provide current, complete, and accurate payment information.'
//       ]
//     },
//     {
//       title: '3. Delivery Policy',
//       list: [
//         '3.1. Delivery Schedule: We will deliver your lunchboxes on the scheduled days per your subscription plan.',
//         '3.2. Missed Delivery: If we fail to deliver due to unforeseen circumstances, we will add one extra day to your subscription period.',
//         '3.3. Customer Responsibility: Ensure the delivery address is correct and someone is available to receive the delivery.'
//       ]
//     },
//     {
//       title: '4. Termination and Refund Policy',
//       list: [
//         '4.1. Termination by User: No refunds for unused portions of the subscription.',
//         '4.2. Termination by Company: We reserve the right to terminate or suspend subscriptions if terms are violated.',
//         '4.3. Company Inability to Deliver: Refunds will be provided only if service cannot be delivered due to company closure or cessation.'
//       ]
//     },
//     {
//       title: '5. User Conduct',
//       content: 'You agree to use our services for lawful purposes only and not engage in conduct that damages our website or services.'
//     },
//     {
//       title: '6. Limitation of Liability',
//       content: 'YumYum Bento will not be liable for indirect, incidental, special, or consequential damages. Total liability will not exceed the last one month’s subscription fees.'
//     },
//     {
//       title: '7. Intellectual Property',
//       content: 'All content on this website is the property of YumYum Bento or its suppliers. Unauthorized use is prohibited.'
//     },
//     {
//       title: '8. Governing Law',
//       content: 'These Terms are governed by the laws of the country in which YumYum Bento operates.'
//     },
//     {
//       title: '9. Changes to Terms',
//       content: 'We may modify these terms at any time. Continued use after changes constitutes acceptance.'
//     }
//   ];

//   return (
//     <>
//       <Navbar navData={navData} />

//       <main className="container py-5">
//         <div className="card p-4 p-md-5" style={{ borderRadius: '10px', boxShadow: '0 4px 12px rgba(0,0,0,0.05)' }}>
//           <h2 className="text-center mb-4" style={{ color: '#993333', fontWeight: 700 }}>Terms and Conditions</h2>
//           <p className="text-muted text-center mb-5">Last updated: September 16, 2025</p>

//           {sections.map((section, idx) => (
//             <section className="mb-5" key={idx}>
//               <h3 style={{ color: '#993333', fontWeight: 700 }}>{section.title}</h3>
//               {section.content && <p>{section.content}</p>}
//               {section.list && (
//                 <ul>
//                   {section.list.map((item, i) => <li key={i}>{item}</li>)}
//                 </ul>
//               )}
//             </section>
//           ))}

//           <p className="mt-5 text-center text-muted">
//             For questions about these terms, contact us at <a href="mailto:support@lunchboxco.com">support@lunchboxco.com</a>.
//           </p>
//         </div>
//       </main>

//       <Footer />

//       {/* Chat Toggle Button */}
//       <button
//         className="btn btn-danger rounded-circle position-fixed"
//         style={{ bottom: 20, right: 20, width: 60, height: 60, zIndex: 999 }}
//         onClick={toggleChat}
//       >
//         💬
//       </button>

//       {/* Chat Box */}
//       {chatOpen && (
//         <div className="card shadow position-fixed" style={{ bottom: 90, right: 20, width: 300, maxHeight: 400, zIndex: 1000, display: 'flex', flexDirection: 'column' }}>
//           <div className="card-header bg-danger text-white">Chat with us</div>
//           <div className="card-body overflow-auto" style={{ flexGrow: 1, maxHeight: 250 }}>
//             {messages.map((msg, idx) => (
//               <div key={idx} className={`mb-2 ${msg.sender === 'user' ? 'text-end' : 'text-start'}`}>
//                 <span className={`badge ${msg.sender === 'user' ? 'bg-primary' : 'bg-secondary'}`}>{msg.text}</span>
//               </div>
//             ))}
//           </div>
//           <form className="card-footer d-flex" onSubmit={handleSendMessage}>
//             <input
//               type="text"
//               className="form-control me-2"
//               placeholder="Type a message..."
//               value={input}
//               onChange={(e) => setInput(e.target.value)}
//             />
//             <button className="btn btn-danger" type="submit">Send</button>
//           </form>
//         </div>
//       )}
//     </>
//   );
// };

// export default Terms;
