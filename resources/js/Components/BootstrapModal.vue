<template>
    <!--password modal-->
    <div class="modal fade" :id="ModalId" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" :class="ModalSize">
            <div class="modal-content" :class="[color === 'dark' ? 'bg-dark text-white' : 'bg-white text-black']">
                <div class="modal-header">
                    <h3 class="modal-title text-gradient-unique">{{ title }}</h3>
                    <button :class="[color === 'dark' ? 'text-white' : 'bg-dark']" type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <slot />

                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: ['ModalId', 'ModalSize', 'title'],
    data() {
        return {
            color: "light", // Default color
        }
    },
    mounted() {
        this.updateColor(); // Initial check for color

        // Set up MutationObserver to watch for class changes on the body
        const observer = new MutationObserver(this.updateColor);
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class'], // Only observe changes to the class attribute
        });

        // Store observer instance to clean up later
        this.observer = observer;
    },
    methods: {
        updateColor() {
            this.color = document.body.classList.contains('dark-version') ? 'dark' : 'light';
        },
    }
}
</script>
