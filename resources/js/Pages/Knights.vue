<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted } from 'vue'

const state = reactive({
	editing_knight: null,
	deleting_knight: null,

	knight: newKnight(),
	knights: [],

	modal_knight_delete_confirm: null
})

const props = defineProps({
    knights: Array,
});

onMounted(() => {
	state.knights = props.knights

	state.modal_knight_delete_confirm = new bootstrap.Modal('#modal_knight_delete_confirm', {})

	// make sure we clear the knight being deleted if we cancel / close the confirm modal
	state.modal_knight_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.deleting_knight = null

		// also clear the form in case we were editing before initializing a delete
		state.knight = newKnight()
		state.editing_knight = null
	})
})

function newKnight()
{
	return {name: null}
}

function editKnight(_knight)
{
	// avoid mutating by reference
	state.editing_knight = JSON.parse(JSON.stringify(_knight))
	state.knight.name = _knight.name
}

function deleteKnight_init(knight)
{
	state.editing_knight = null
	state.deleting_knight = knight

	state.modal_knight_delete_confirm.show()
}

function deleteKnight()
{

	axios.delete(route('knights_delete', {knight: state.deleting_knight.id}))
		.then(res => {

			state.knights = state.knights.filter(k => k.id != state.deleting_knight.id)

			state.deleting_knight = null

			state.modal_knight_delete_confirm.hide()

		})
		.catch(err => {
			debugger
		})
}

function cancelEdit()
{
	state.editing_knight = null
	state.knight = newKnight()
}

function saveKnight()
{
	if (state.editing_knight && state.editing_knight.id)
	{

	    axios.put(
	    	route('knights_update', {knight: state.editing_knight.id}),
	    	{ name: state.knight.name, }
	    )
	    .then(res => {
	    	const k = res.data.knight

	    	for (let i = 0; i < state.knights.length; i++)
	    	{
	    		if (state.knights[i].id === k.id)
	    		{
	    			state.knights[i] = k
	    		}
	    	}

	    })
	    .catch(err => {
	    	debugger
	    })

		cancelEdit()
		return
	}

    axios.post(route('knights_store', {name: state.knight.name}))
	    .then(res => {

	    	// clear the form
	    	state.knight = newKnight()
	    	state.knights.push(res.data.knight)
	    })
	    .catch(err => {
	    	debugger
	    })

}

</script>

<template>
    <AppLayout title="Knights">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Knights
            </h2>
        </template>

        <div class="py-12">
	        <!-- Knight Form -->
			<div class="mb-3 p-4 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
				<div class="mx-auto flex">
					<div class="flex-1 px-2">
						<h2 class="heading">Manage Your Knights</h2>
						<p class="">
							Use the form to create and edit Knights.
						</p>
					</div>
					<div class="flex-1 px-2 d-md-flex align-items-end">
						<div class="flex-1 mb-3 mb-md-0">
							<label for="name" class="form-label">{{ state.editing_knight ? "Editing " + state.editing_knight.name : 'Knight Name' }}</label>
							<input
								type="text"
								class="form-control"
								id="name"
								placeholder="e.g. Silver Knight"
								v-model="state.knight.name"
								>
						</div>
						<div class="ms-3 mb-3 mb-md-0">
							<button
								type="button"
								class="btn btn-light"
								@click="saveKnight"
								>
								Save
							</button>
						</div>
						<div class="ms-3 mb-3 mb-md-0">
							<button
								type="button"
								class="btn btn-light"
								@click="cancelEdit"
								>
									Cancel
								</button>
						</div>
					</div>
				</div>
			</div>

	        <!-- Knight List -->
			<div class="mb-3 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
                <div class="list-group">
                	<div v-for="_knight in state.knights" :key="_knight.id"
                	class="list-group-item py-3"
                	style="
	                	display: flex;
	                	align-items: center;
	                	justify-content: space-between;
                	"
                	>
	                	<!-- LEFT section -->
                		<div class="ln-list-item__left">
                			<div class="heading">
		                		{{_knight.name}}
                			</div>
                			<div class="sub-heading">
                				Some subtext here. Perhaps a longer description.
                			</div>
                		</div>

                		<!-- RIGHT section -->
                		<div class="ln-list-item__right">

                			<!-- Actions are laid our horizontally -->
                			<!-- All buttons are wrapped in divs to make sure FLEX works consistently across all browsers -->
                			<div class="ln-list-item__action me-2">
                				<button
                					class="btn btn-outline-secondary"
                					type="button"
                					@click="editKnight(_knight)"
                					>
                						Edit
            					</button>
                			</div>

                			<!-- Confirm before deleting a Knight -->
                			<div class="ln-list-item__action">
                				<button
                					class="btn btn-danger"
                					type="button"
                					@click="deleteKnight_init(_knight)"
                					>
                						Delete
            					</button>
                			</div>

                		</div>
                	</div>
                </div>
            </div>

			<!-- Confirm Delete Modal -->
			<div class="modal fade" id="modal_knight_delete_confirm" tabindex="-1" aria-labelledby="modal_knight_delete_confirm_label" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="modal_knight_delete_confirm_label">Delete a Knight</h5>
			        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			      </div>
			      <div class="modal-body">
			        Are you sure you want to delete this Knight?
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteKnight">Confirm</button>
			      </div>
			    </div>
			  </div>
			</div>
			<!-- End Confirm Delete Modal -->

        </div>
	</AppLayout>
</template>
