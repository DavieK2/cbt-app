<script>
    export let buttonText;
</script>

<button class="flex items-center px-4 py-3 font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-gray-900 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-80">
    <slot></slot>
    <span class="mx-1 text-sm">{ buttonText }</span>
</button>