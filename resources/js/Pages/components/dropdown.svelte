<script>
    import { fade } from "svelte/transition";
    import Icons from "./icons.svelte";
    import { cn } from "./utils";
    import { quintIn, backOut } from "svelte/easing";


    export let placeholder
    export let className = '';
    export let arrowColor = "fill-white";

    let showDropdown = false

    const show = () => showDropdown = ! showDropdown
    const hide = () => setTimeout(() => showDropdown = false, 200)

</script>

<div class="relative overflow-visible">
    
    <div class="min-w-max">
        <button  on:focus={ show } on:focusout={ hide } name="" class={ cn(`block w-full rounded-lg border-0 py-2.5 px-3 bg-gray-800 text-white  placeholder:text-gray-400 focus:ring-inset focus:ring-gray-800 text-xs sm:leading-6`, className ) }>
            <div class="flex items-center justify-between space-x-3">
                <span >{ placeholder }</span>
                <div class="flex items-center justify-center space-x-2">
                    <div class="h-5 w-px bg-gray-300"></div>
                    { #if showDropdown }
                        <Icons icon="chevron_up" className={ arrowColor } />
                    { :else }
                        <Icons icon="chevron_down" className={ arrowColor } />
                    { /if }
                </div>
               
            </div>
        </button>  
    </div>
    
    { #if showDropdown }
        <div out:fade={{ duration: 100, easing: backOut }} in:fade={{ duration: 100, easing: quintIn }} class="absolute isolate right-0 origin-top-right flex flex-col items-start p-3 min-w-max w-full min-h-max max-h-min mt-3  bg-white border rounded-lg z-50 text-gray-700">
            <div class="flex flex-col h-full w-full overflow-y-auto">
                <slot></slot>
            </div>
        </div>
    { /if }
</div>