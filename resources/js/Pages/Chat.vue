<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, nextTick} from 'vue'

const props = defineProps({
    friend : {
        type: Object,
        default: {}
    },
    currentUser : {
        type: Object,
        default: {}
    }
})

const messages = ref([]);
const newMessage = ref([]);
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);

watch(messages, () => {
    nextTick(() => {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior : 'smooth'
        })
    });
}, {
    deep: true
})

const sendMessage = async () => {
    let response = await axios.post(route('send-messages', props.friend.id), {
        'text' : newMessage.value,
    });
    messages.value.push(response.data);
    newMessage.value = '';
}

const sendTypingEvent= () => {
    Echo.private(`chat.${props.friend.id}`).whisper('typing', {
        userId : props.currentUser.id
    })
}

onMounted(async () => {
   let response = await axios.get(route('get-messages', props.friend ));
    if (response.data) {
        messages.value = response.data.data;
    }

    Echo.private(`chat.${props.currentUser.id}`)
    .listen('MessageSendEvent', (response) => {
        messages.value.push(response.message)
    })
    .listenForWhisper('typing', (response) =>{
        isFriendTyping.value = response.userId === props.friend.id;

        if (isFriendTypingTimer.value) {
            clearTimeout(isFriendTypingTimer.value)
        }
        isFriendTypingTimer.value = setTimeout(() => {
            isFriendTyping.value = false
        }, 1000);
    })
});


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                {{friend.name}}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                         <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-white">
                            <div>
                                <div class="flex flex-col justify-end h-80">
                                    <div ref="messagesContainer" class="p-4 overflow-y-auto max-h-fit">
                                        <div
                                            v-for="message in messages"
                                            :key="message.id"
                                            class="flex items-center mb-2"
                                        >
                                            <div
                                                v-if="message.sender_id === currentUser.id"
                                                class="p-2 ml-auto text-white bg-blue-500 rounded-lg"
                                            >
                                                {{ message.text }}
                                            </div>
                                            <div v-else class="p-2 mr-auto bg-gray-200 rounded-lg">
                                                {{ message.text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        type="text"
                                        v-model="newMessage"
                                        @keydown="sendTypingEvent"
                                        @keyup.enter="sendMessage"
                                        placeholder="Type a message..."
                                        class="flex-1 px-2 py-1 border rounded-lg"
                                    />
                                    <button
                                        @click="sendMessage"
                                        class="px-4 py-1 ml-2 text-white bg-blue-500 rounded-lg"
                                    >
                                        Send
                                    </button>
                                </div>
                                <small v-if="isFriendTyping" class="text-gray-700">
                                    {{ friend.name }} is typing...
                                </small>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

