<script>
    import { createEventDispatcher } from "svelte";
    import Button from "../components/button.svelte";
    import Icons from "../components/icons.svelte";
    import Select from "../components/select.svelte";

    export let disabled = false;

    export let headings;
    export let options;


    let mappedFields = {}


    $: getAvailableMappings = () => {

        return options?.filter( (field) => field.isSelected == false );
    }


    const dispatch = createEventDispatcher();



    const mapFields = (selected) => {

        let field = options.filter( (field) => (selected.value === field.value) )[0];
        
        if( field ){
            field.isSelected = ! field.isSelected ;
        }

        if( (selected.selected)){
            mappedFields[selected.value] = Object.keys(headings)[selected.index]
        }
        
    
        if( (selected.deselected) ){
            delete mappedFields[selected.value];
        }

        options = options;
    }

    const onMap = (selected) => {

        mapFields(selected);

        dispatch('updated', mappedFields);
    }

</script>

<div class="flex flex-col px-3 pb-5">
    <div class="flex items-center space-x-3 mb-5 py-5">
        <button on:click={ () => dispatch('back-button') }>
            <Icons icon="back_arrow" />
        </button>
        <h1 class="font-bold text-lg text-gray-800">Map Relevant Rows</h1>
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
                        <Select on:deselected={ (e) => onMap( { ...e.detail, index, selected: false } ) } on:selected={ (e) => onMap( { ...e.detail, index, selected: true } )  } placeholder="Select Field" options={ getAvailableMappings() } />
                     </div>
                {/each}
            </div>
        </div>
    </div>

   
    <slot></slot>
   

    <div class="flex mt-10">
        <Button on:click={ () => dispatch('import', mappedFields) } buttonText="Import" { disabled } className="max-w-min min-w-max" />
    </div>
</div>

