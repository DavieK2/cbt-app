<script>
    import { cn } from "../../components/utils";

    export let color;
    export let qNumber = "";
    
</script>

<div style={"border-color:" + color } class={ cn("flex shrink-0 flex-col h-10 w-10 rounded-sm border border-green-400 overflow-hidden justify-between")}>
    <span class="flex items-center justify-center text-xs font-medium w-full h-full text-gray-500">{ qNumber ?? 0 }</span>
    <span style={"background-color:" + color }  class={ cn("flex h-2.5 w-full bg-green-400") }></span>
</div>

