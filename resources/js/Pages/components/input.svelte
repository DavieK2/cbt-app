<svelte:options accessors />

<script>
    import { createEventDispatcher } from "svelte";
    import { cn } from "./utils";

    export let disabled = false;
    export let value = '';
    export let pattern = '';
    export let label = '';
    export let type = 'text';
    export let placeholder = '';
    export let labelStyle = '';
    export let className = '';

    const dispatch = createEventDispatcher();

    const onInput = (input) => {
        value = input;
        dispatch('input', { input });
    }
</script>

<div class="space-y-3">
    { #if label }
        <label for="" class={ cn("text-sm text-gray-700", labelStyle )}>{ label }</label>
    {/if}
    <input { value } { pattern } on:input={ (e) => onInput(e.target.value) } { type } class={ cn("block w-full rounded-lg border-0 py-2.5 px-2.5 text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6", className) } { placeholder } { disabled }>
</div>
