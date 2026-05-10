import { onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotificationStore } from '@/stores/notification';
import { useI18n } from 'vue-i18n';

export function useRealtime() {
    const page = usePage();
    const notificationStore = useNotificationStore();
    const { t } = useI18n();

    const user = computed(() => page?.props?.auth?.user);

    const setupListeners = () => {
        if (!user.value || !window.Echo) return;

        // Private channel for the user
        window.Echo.private(`App.Models.User.${user.value.id}`)
            .notification((notification) => {
                notificationStore.info(notification.message, notification.title || t('notifications.new_notification'));
            });

        // Role-based channels
        if (user.value.role === 'courier') {
            window.Echo.private('courier.available')
                .listen('NewDeliveryAvailable', (e) => {
                    notificationStore.info(e.message, t('notifications.new_delivery'));
                });
        }
    };

    const leaveChannels = () => {
        if (!user.value || !window.Echo) return;
        window.Echo.leave(`App.Models.User.${user.value.id}`);
        if (user.value.role === 'courier') {
            window.Echo.leave('courier.available');
        }
    };

    // Listen for OrderStatusChanged if we have active orders
    const listenToOrder = (orderId) => {
        if (!window.Echo) return;
        window.Echo.private(`orders.${orderId}`)
            .listen('OrderStatusChanged', (e) => {
                notificationStore.success(e.message, t('notifications.order_update'));
            });
    };

    const leaveOrder = (orderId) => {
        if (!window.Echo) return;
        window.Echo.leave(`orders.${orderId}`);
    };

    // Listen for DeliveryStatusChanged
    const listenToDelivery = (deliveryId) => {
        if (!window.Echo) return;
        window.Echo.private(`deliveries.${deliveryId}`)
            .listen('DeliveryStatusChanged', (e) => {
                notificationStore.info(e.message, t('notifications.delivery_update'));
            });
    };

    const leaveDelivery = (deliveryId) => {
        if (!window.Echo) return;
        window.Echo.leave(`deliveries.${deliveryId}`);
    };


    return {
        setupListeners,
        leaveChannels,
        listenToOrder,
        leaveOrder,
        listenToDelivery,
        leaveDelivery
    };
}
