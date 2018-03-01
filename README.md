# YouTube-Live-Chatroom
YouTube Live Chatroom Customizer - Wordpress Plugin 

Allows users to embed a custom chatroom which is populated with the messages from the YouTube Livechat API. Live messages are shown in the chatroom and users can submit chat messages directly through the embedded chatroom (instead of through YouTube directly). 



1 - Plugin manages YouTube Login and API Connection for user
	* One time log-in with Google Credentials and O-Auth to use YouTube API
	* Database would store chatroom data so that chats are available forever scrolling through

2-Wordpress Plugin Management
	1-Wordpress management panel allows users to “create chatroom” which asks for video link.
	2-Dashboard displays various standard information received back from YouTube API
	3-Chatroom would have a few classes that can be altered with a css insert box 

3-App also generates XML file with previous 20 live chats in ascending order where the most recent chat is always message 1… Then the second most recent chat is moved to message 2 and so forth…
