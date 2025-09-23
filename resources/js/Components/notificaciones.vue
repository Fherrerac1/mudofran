<template>
    <li class="nav-item dropdown d-flex align-items-center">
        <a href="#" class="p-0 nav-link lh-1" :class="[color ? color : 'text-body', showNotifications ? 'show' : '']"
            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" @click="toggleNotifications">
            <i class="material-icons cursor-pointer">notifications</i>
            <span v-if="notificationCount > 0" class="badge bg-danger">{{ notificationCount }}</span>
        </a>
        <ul class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4" :class="showNotifications ? 'show' : ''"
            aria-labelledby="dropdownMenuButton">
            <li v-if="notifications.length === 0" class="text-center mb-2">
                <span class="dropdown-item text-muted">No hay nuevas notificaciones</span>
            </li>
            <li v-for="(notification, index) in notifications" :key="index" class="mb-2">
                <a class="dropdown-item border-radius-md"
                    :href="getNotificationLink(notification.tipo, notification.post_id)">
                    <div class="py-1 d-flex">
                        <div class="my-auto">
                            <!-- Display an image or fallback icon -->
                            <img v-if="notification.image" :src="notification.image" class="avatar avatar-sm me-3"
                                alt="notification image" />
                            <div v-else class="avatar avatar-sm bg-gradient-secondary me-3">
                                <i class="material-icons">person</i>
                            </div>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-1 text-sm font-weight-normal">
                                <span class="font-weight-bold">{{ notification.title }}</span>
                            </h6>
                            <p class="mb-0 text-xs text-secondary">
                                <i class="fa fa-clock me-1"></i>
                                {{ notification.content }}
                            </p>
                        </div>
                    </div>
                </a>
            </li>
        </ul>
    </li>
</template>


<script>
import axios from 'axios';
import Pusher from 'pusher-js';

export default {
    data() {
        return {
            showNotifications: false,
            notifications: [],
            liveInterval: null,
        };
    },
    props: ['user', 'color'],
    computed: {
        notificationCount() {
            return this.notifications.length;
        },
    },
    created() {
        this.liveData();
        this.initializeEcho();
    },
    methods: {
        initializeEcho() {
            Pusher.logToConsole = false;

            const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            });

            const channel = pusher.subscribe('notifications-live');
            channel.bind('notifications-updated', ({ notification }) => {
                if (
                    !notification ||
                    this.notifications.some(n => n.id === notification.id) ||
                    (notification.viewers?.includes(this.user.id)) ||
                    // 🚨 Filter by tenant_id
                    notification.tenant_id !== this.user.tenant_id ||
                    (
                        ['admin', 'gestor'].includes(this.user.rol)
                            ? notification.user_id !== null && notification.user_id !== this.user.id
                            : notification.user_id !== this.user.id
                    )
                ) return;

                this.notifications.push(notification);
            });
        }
        ,
        getNotificationLink(tipo, postId) {
            const routes = {
                '1': `/nominas/${postId}/mostrar`,
                '2': `/bajas/${postId}/mostrar`,
                '3': `/time-off/${postId}/mostrar`,
                '4': `/observaciones/${postId}/mostrar`,
            };
            return routes[tipo] || '/';
        },
        toggleNotifications() {
            this.showNotifications = !this.showNotifications;
        },
        closeNotifications() {
            this.showNotifications = false;
        },
        async liveData() {
            try {
                const response = await axios.get('/mis-notificaciones');
                this.notifications = response.data;
                this.notifications = Object.values(this.notifications).reverse();
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },
    },
    mounted() {
    },
    beforeDestroy() {
        clearInterval(this.liveInterval);
    },
};
</script>
