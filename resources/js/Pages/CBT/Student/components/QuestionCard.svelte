<script>
    import Icons from "../../../components/icons.svelte";
    import { createEventDispatcher, onMount } from "svelte";

    export let questionNumber;
    export let question = {};

    let options = [];

    onMount(() => {
        options = shuffle(question.choices ?? []);
    });

    const getAlphabetsOptions = () => {

        let alphabet = [];

        for( let i = 65 ; i <= 90 ; i++){
            alphabet.push(String.fromCharCode(i));
        }

        return alphabet;
    }

    const dispatch = createEventDispatcher();

    const shuffle = (array) => {

        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }

        return array;
    }

</script>

<div class="flex flex-col px-8 pt-8 pb-4">
    <div class="pb-6">
        <div class="space-y-4 pb-3">
           <div class="flex items-center justify-between">
                <p class="text-gray-800 font-bold text-lg">Question { questionNumber }</p>
           </div>
            <p class="text-gray-800 text-base font-base pt-6">{ @html question.prompt }</p>
        </div>
        <ul class="flex flex-col w-full space-y-2 text-sm text-gray-600 pt-8">
            { #each options as option, index }
                <div class="flex space-x-2 items-center w-full text-base">
                    <button  on:click={ () => dispatch('selected', { questionId : question.questionId, option: option.content }) } class={`flex items-center shrink-0 justify-center h-[3.2rem] w-[3.2rem] border-2 ${ option.content == question.selectedAnswer ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                        <span class={`${ option.content == question.selectedAnswer ? "text-green-700" : "text-gray-500" } font-medium`} > { getAlphabetsOptions()[index] }</span>
                    </button>
                    <div class="w-full">
                        <button type="button" class={`flex p-3 border-2 ${ option.content == question.selectedAnswer ? 'border-green-700' : 'border-gray-300' }  w-full rounded-lg items-center justify-between space-x-2`}>
                            <span class={`${ option.content == question.selectedAnswer ? "text-green-700" : "text-gray-500" } text-left`} >  { @html option.htmlContent} </span>
                        </button>
                    </div>
                </div>
            { /each }
        </ul>
       <slot></slot>
    </div>
</div>