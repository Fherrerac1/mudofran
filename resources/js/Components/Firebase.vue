<template>
    <!-- Your template here, if needed -->
</template>

<script>
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import axios from 'axios';

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: 'AIzaSyAhm1t_YFPNah7djSlMVOgA1bnlPFh-Y1U',
    authDomain: 'psicologos-c297f.firebaseapp.com',
    projectId: 'psicologos-c297f',
    storageBucket: 'psicologos-c297f.appspot.com',
    messagingSenderId: '1091444708343',
    appId: '1:1091444708343:web:785a71e5382b5d36a114f2',
    measurementId: 'G-VXM6192GQK',
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export default {
    name: 'FirebaseToken',

    data() {
        return {
            token: null,
            permission: false,
        };
    },

    methods: {
        async requestPermission() {
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    this.permission = true;
                    this.getToken();
                } else {
                    console.log('Notification permission denied.');
                }
            } catch (error) {
                console.error('Error requesting notification permission:', error);
            }
        },

        async getToken() {
            try {
                const currentToken = await getToken(messaging, {
                    vapidKey: 'BNzpdNecbYKWzP-kjizo88Qd0IrTzE_Z9LxlOVomH5NOY-x-pIFEtW3G1MjPb9t2u73jMVOt2hzWUR1Ke-I7yPE',
                });

                if (currentToken) {
                    this.token = currentToken;
                    // Send the token to the server
                    await this.updateTokenOnServer(currentToken);
                } else {
                    console.log('No registration token available. Request permission to generate one.');
                }
            } catch (error) {
                console.error('Error getting FCM token:', error);
            }
        },

        async updateTokenOnServer(token) {
            try {
                // Replace '/fcm-token' with the correct API endpoint in your Laravel app
                await axios.patch('/fcm-token', { token }, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                        'Content-Type': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Error sending token to server:', error);
            }
        }
    },

    mounted() {
        // Request notification permission and handle foreground messages
        this.requestPermission();

        onMessage(messaging, (payload) => {
            console.log('Message received in foreground:', payload);
            const { title, body } = payload.notification || {};
            if (Notification.permission === 'granted' && title && body) {
                new Notification(title, { body });
            }
        });
    }
};
</script>

<style scoped>
/* Add your styles here, if needed */
</style>