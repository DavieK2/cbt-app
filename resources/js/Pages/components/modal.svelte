<script>
    import { fade } from "svelte/transition";

    export let show = false;
</script>

{ #if show }
    <div in:fade="{{ delay: 50, duration: 100 }}" class="fixed flex flex-col items-center justify-center min-h-screen w-full left-20 inset-y-0 right-0 bg-gray-800/10 z-40 backdrop-blur-sm">
        <div class="bg-white  min-h-[20rem] min-w-[20rem] rounded-lg p-8">
            <slot></slot>
        </div>
    </div>
{  /if }