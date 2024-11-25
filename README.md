Realtime chat application with Laravel reverb , Tailwind and Vue 3 composition api
First install the laravel 
Install laravel Reverb using php artisan install:broadcasting install reverb and node js packages
Create Event using php artisan make:event EventName and inside the event file, provide a event name
```
public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->message->receiver_id}"),
        ];
    }
```
Then , in the route/channels.php file, Add the event and condition 
```
Broadcast::channel('chat.{id}', function ($user, $id) {
    return <condition>
});
```
call the event inside the controller ``` broadcast(new MessageSendEvent())```
And in the vue file, inside the onMount method add the listner to listen to the event. as well as we can add wisper function
```
const messages = ref([]);
Echo.private(`chat.${id}`).whisper( 'typing' => {
    userId : props.currentUser.id
})
onMounted(()) => {
    Echo.private('chat,${id}')
    .listen((response) => {
        messages.value.push(response.message)
    })
    .listenForWhisper('typing', (response) => {
        isFrindTyping.value = response.userId === props.friend.id
    });

    if (isFriendTypingTimer.value) {
        clearTimeout(isFriendTypingTimer.value)
    }
    isFriendTypingTimer.value = setTimeout(() => {
        isFriendTyping.value = false
    }, 1000);
    
```
