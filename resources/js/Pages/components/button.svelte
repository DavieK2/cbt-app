<script>
    import { cn } from "./utils";

    export let buttonText;
    export let className = "";
    export let disabled = false

</script>

<button { disabled } on:click class={ cn("flex items-center justify-center w-full px-4 py-2.5 tracking-wide text-white transition-colors duration-300 transform bg-gray-800 rounded-lg hover:bg-gray-700 focus:outline-none focus:bg-gray-800 focus:ring focus:ring-gray-600 focus:ring-opacity-50 disabled:bg-gray-500", className )}>
   { buttonText }
</button>