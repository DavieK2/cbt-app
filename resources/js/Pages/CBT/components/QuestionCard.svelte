<script>
    import { createEventDispatcher } from "svelte";

    export let questionNumber;
    export let sectionTitle;
    export let question;
    export let hasBeenAssigned;
    export let questionId;
    export let questionOptions = []
    export let correctAnswer;
    export let questionScore
    export let showOptions = true;
    export let showCheckBox = false;
    export let checked = false;

    const dispatch = createEventDispatcher();
</script>

<div class={`${ showCheckBox ? 'pr-8 pl-12' : 'px-8' } flex flex-col ${hasBeenAssigned == 'Assigned' ? 'bg-green-50 ' : '' } mt-8 mb-4 w-full`}>
    <div class="border-b p-6  rounded-lg">
        <div class="space-y-4 pb-3">
           <div class="flex items-center justify-between min-w-md shrink-0 max-w-lg">
                { #if showCheckBox }
                    <div class="flex space-x-8 -ml-12 items-center">
                        <input checked={ checked } on:input={ (e) => e.target.checked ? dispatch('checked', { value: e.target.value }) : dispatch('unchecked', { value: e.target.value }) } class="cursor-pointer p-2" type="checkbox" value={ questionId } >
                        <p class="text-gray-600 font-bold text-base">Question { questionNumber }</p>
                    </div>
                { :else }
                    <p class="text-gray-600 font-bold text-base">Question { questionNumber }</p>
                {/if}
                <p class="text-gray-500 font-semibold text-xs">( { sectionTitle ?? 'No Section' } | { questionScore } Marks )</p>
           </div>
            <div class="text-gray-700 text-sm font-normal min-w-md shrink-0 max-w-md">{ @html question }</div>
        </div>
        { #if showOptions }
            <ul class="flex flex-col w-full space-y-2 text-xs text-gray-600 pt-6">
                <p class="font-semibold text-base text-gray-500">Options</p>
                { #each questionOptions as option }
                    { #if option.content == correctAnswer }
                        <div class="flex p-3 border-2 border-green-500 min-w-md shrink-0 max-w-lg rounded-lg items-center justify-between space-x-2">
                            <div>
                                { @html option.htmlContent }
                            </div>
                            <small class="text-green-500">(Correct Answer)</small>
                        </div>
                    { :else }
                        <div class="flex p-3 border-2 border-gray-300 min-w-md shrink-0 max-w-lg rounded-lg items-center justify-between space-x-2">
                            
                            <div>
                                { @html option.htmlContent }
                            </div>
                        
                        </div>
                    { /if }
                { /each} 
            </ul>
        { /if }
       
       <slot></slot>
    </div>
</div>