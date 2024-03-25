<svelte:options accessors />

<script>
    import Pikaday from 'pikaday';
    import moment from 'moment';
    import { createEventDispatcher, onMount } from 'svelte';
    import { cn } from './utils';

    export let datetime = '';
    export let value;
    export let label = '';
    export let placeholder = '';
    export let showLabel = true;
    export let labelStyling = '';
    export let className = '';

    let date;
    let hour = "00";
    let min = "00";
    let sec = "00";

    let hours = []
    let mins = []


    const dispatch = createEventDispatcher();

    onMount(() => {
        hours = getTimes(23);
        mins = getTimes(59);
    })

    const getTimes = (length) => {

        let array = [];

        for (let i = 0; i <= length; i++) {
           
            let x = i.toString();

            if(x.length < 2){
                x = "0" + x;
            }

            array.push(x);
            
        }

        return array;
    }

    function datepicker(node) {

        setTimeout(() => {

            if( value ){
                hour = moment(value).hour().toString().padStart(2, '0');
                min = moment(value).minute().toString().padStart(2, '0');
            }

            let picker = new Pikaday({ 
                field : node,
                format: 'D MMM YYYY',
                onSelect: function() {
                    date = moment(this.getDate()).format('YYYY-MM-DD')  
                }
            });

            if( value ) picker.setDate(value);   

        }, 400);
        

        
    }

    $ : {
        datetime = `${ date } ${hour}:${min}:${sec}`;
        dispatch('selected', { datetime });
    }

</script>

<div class="flex flex-col space-y-3 justify-center">

   { #if showLabel }
        <label for="" class={ cn("font-semibold text-sm", labelStyling) }>{ label }</label>
   {  /if }

    <div class="flex space-x-3">
        <input use:datepicker type="text" class="form-field rounded-lg" { placeholder }>
        <div class="flex space-x-1 items-center">
            <select on:change={ () => dispatch('selected', { datetime }) } bind:value={ hour } name="" id="" class="block w-12 text-center rounded-lg border-0 py-2.5 px-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                { #each hours as hour}
                    <option value={ hour }>{ hour }</option>
                { /each }
              </select> 
             <span>:</span>
              <select on:change={ () => dispatch('selected', { datetime }) } bind:value={ min } name="" id="" class="block w-12 text-center rounded-lg border-0 py-2.5 px-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                { #each mins as min}
                    <option value={ min }>{ min }</option>
                { /each }
              </select> 
        </div>
    </div>
</div>


<style>
    select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    /* background: url(https://stackoverflow.com/favicon.ico) 96% / 15% no-repeat #EEE; */
  }
</style>