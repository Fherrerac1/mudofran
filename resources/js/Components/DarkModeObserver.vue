<template>
    <div></div> <!-- No UI needed, just functionality -->
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';

export default {
    name: 'DarkModeObserver',
    emits: ['darkModeChange'],
    setup(props, { emit }) {
        const darkMode = ref(false);

        const updateDarkMode = () => {
            darkMode.value = document.body.classList.contains('dark-version');
            emit('darkModeChange', darkMode.value); // Emit dark mode status to parent component
        };

        onMounted(() => {
            // Create a MutationObserver to watch for class changes on the body
            const observer = new MutationObserver(() => {
                updateDarkMode();
            });

            // Observe changes to the class attribute on the body
            observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

            // Set the initial dark mode status
            updateDarkMode();

            // Cleanup the observer when the component is unmounted
            onBeforeUnmount(() => {
                observer.disconnect();
            });
        });

        return { darkMode };
    }
};
</script>