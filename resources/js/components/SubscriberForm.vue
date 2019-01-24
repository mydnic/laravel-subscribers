<template>
    <div>
        <div class="flex items-center border-b border-b-2 border-white py-2">
            <input type="email"
                name="email"
                class="appearance-none text-white bg-transparent border-none w-full text-grey-darker mr-3 py-1 px-2 leading-tight focus:outline-none"
                placeholder="Enter your email"
                v-model="email"
            >
            <button
                class="flex-no-shrink text-lg bg-transparent hover:bg-white border border-white text-sm hover:text-primary text-primary py-2 px-3 rounded-full"
                type="button"
                @click="subscribe"
                :disabled="isLoading"
                :class="{'loading': isLoading}"
            >
                Subscribe
            </button>
        </div>
        <p class="text-green-light mt-4" v-if="success">
            Thank you for subscribing!
        </p>
        <p class="text-red text-sm mt-4" v-if="errors.email" v-text="errors.email[0]"></p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            email: undefined,
            errors: [],
            isLoading: false,
            success: false,
        }
    },
    methods: {
        subscribe() {
            this.isLoading = true;
            this.errors = [];
            this.success = false;
            axios.post('/api/subscriber', {
                email: this.email
            })
            .then(response => {
                this.isLoading = false;
                this.success = true;
                this.email = undefined;
            })
            .catch(error => {
                this.errors = error.response.data.errors;
                this.isLoading = false;
            });
        }
    },
}
</script>

<style scoped>
    input::placeholder{
        color: white !important;
    }
</style>
