<script setup>
import { reactive, computed } from 'vue'

const state = reactive({
	baseClasses: [ 'modal', 'fade' ]
})

const props = defineProps({
	classes: String,
	id: String,
})

const finalClasses = computed(() => {
	return state.baseClasses.join(' ') + ' ' + props.classes
})

</script>

<template>
	<div :class="finalClasses"
		:id="props.id"
		tabindex="-1"
		:aria-labelledby="props.id + '_label'"
		aria-hidden="true"
	>
		<div class="modal-dialog">
		    <div class="modal-content">
			    <div class="modal-header">
			        <h5 class="modal-title" :id="props.id + '_label'">
			        	<slot name="title"></slot>
			        </h5>
			        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		      	</div>
		      	<div class="modal-body">

		      		<slot name="body"></slot>

		      	</div>
		      	<div class="modal-footer">
		      		<slot name="footer"></slot>
		      	</div>
		    </div>
		</div>
	</div>
</template>