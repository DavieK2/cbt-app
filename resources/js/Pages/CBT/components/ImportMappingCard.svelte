<script>
    import { createEventDispatcher } from "svelte";
    import Select from "../../components/select.svelte";
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";

    export let disabled = false;

    export let headings;
    export let options;
    export let sections = [];
    export let questionTypes = [];

    const dispatch = createEventDispatcher();

</script>

<div class="flex flex-col px-3 pb-5">
    <div class="flex items-center space-x-3 mb-5 py-5">
        <button on:click={ () => dispatch('back-button') }>
            <Icons icon="back_arrow" />
        </button>
        <h1 class="font-bold text-lg text-gray-800">Map Questions</h1>
    </div>
    <div class="flex space-y-4 flex-col px-6 pt-6 pb-12 border-t border-e border-x rounded-t-lg">
        <Select on:selected={ (e) => dispatch('section', { value: e.detail.value }) } on:deselected={ (e) => dispatch('de-section', { value: null }) } options={ sections } className="text-sm py-2.5" placeholder="Select Section" label="Select Section" labelStyle="font-semibold text-sm text-gray-800" />
        <Select on:selected={ (e) => dispatch('question-type', { value: e.detail.value }) } on:deselected={ (e) => dispatch('de-question-type', { value: null }) } options={ questionTypes } className="text-sm py-2.5" placeholder="Select Question Type" label="Select Question Type" labelStyle="font-semibold text-sm text-gray-800" />
    </div>
    <div class="p-6 border rounded-b-lg">
        <div class="grid grid-cols-3 w-full text-sm shrink-0 gap-6 font-semibold pb-6" >
            <div>Headings</div>
            <div>Values</div>
            <div>Mapping</div>
        </div>
        <div class="border-b -mx-6"></div>
        <div class="grid grid-cols-3 w-full text-sm shrink-0 gap-6 text-gray-700 mt-4" >
            <div class="flex flex-col mb-6">
               { #each Object.keys(headings) as heading }
                    <div class="flex flex-col justify-center break-words h-16">{ heading }</div>
               {/each}
            </div>
            <div class="flex flex-col mb-6">
                { #each Object.values(headings) as heading }
                     <div class="flex flex-col justify-center break-all h-16">{ heading.toString().length > 15 ? ( heading.toString().substring( 0, 15 ) + "...") : heading.toString() }</div>
                {/each}
            </div>
            <div class="flex flex-col">
                { #each Object.values(headings) as heading, index }
                     <div class="flex flex-col justify-center break-all h-16">
                        <Select on:deselected={ (e) => dispatch('deselected', { ...e.detail, index, deselected: true }) } on:selected={ (e) => dispatch('selected', { ...e.detail, index, selected: true }) } placeholder="Select Field" { options } />
                     </div>
                {/each}
            </div>
        </div>
    </div>
    <div class="flex mt-10">
        <Button on:click={ () => dispatch('import') } buttonText="Import Questions" { disabled } className="max-w-min min-w-max" />
    </div>
</div>