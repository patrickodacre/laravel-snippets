<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted } from 'vue'

const state = reactive({
	editing_client: null,
	deleting_client: null,

	client: newClient(),
	clients: [],

	modal_client_delete_confirm: null
})

const props = defineProps({
    clients: Array,
});

onMounted(() => {
	state.clients = props.clients

	state.modal_client_delete_confirm = new bootstrap.Modal('#modal_client_delete_confirm', {})

	// make sure we clear the client being deleted if we cancel / close the confirm modal
	state.modal_client_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_client = null

		// also clear the form in case we were editing before initializing a delete
		state.client = newClient()
		state.deleting_client = null
	})
})

function newClient()
{
	return {
		name_first: null,
		name_last: null,
		description: null,
		email: null,
	}
}

function editClient(_client)
{
	// avoid mutating by reference
	state.editing_client = JSON.parse(JSON.stringify(_client))
	state.client = JSON.parse(JSON.stringify(_client))
}

function deleteClient_init(client)
{
	state.editing_client = null
	state.deleting_client = client

	state.modal_client_delete_confirm.show()
}

function deleteClient()
{

	axios.delete(route('clients_delete', {client: state.deleting_client.id}))
		.then(res => {

			state.clients = state.clients.filter(k => k.id != state.deleting_client.id)

			state.deleting_client = null

			state.modal_client_delete_confirm.hide()

		})
		.catch(err => {
			debugger
		})
}

function cancelEdit()
{
	state.editing_client = null
	state.client = newClient()
}

function saveClient()
{
	if (state.editing_client && state.editing_client.id)
	{

	    axios.put(route('clients_update', {client: state.editing_client.id}), state.client)
	    .then(res => {
	    	const k = res.data.client

	    	for (let i = 0; i < state.clients.length; i++)
	    	{
	    		if (state.clients[i].id === k.id)
	    		{
	    			state.clients[i] = k
	    		}
	    	}

	    })
	    .catch(err => {
	    	debugger
	    })

		cancelEdit()
		return
	}

    axios.post(route('clients_store', state.client))
	    .then(res => {

	    	// clear the form
	    	state.client = newClient()
	    	state.clients.push(res.data.client)
	    })
	    .catch(err => {
	    	debugger
	    })

}

</script>

<template>
    <AppLayout title="clients">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Clients
            </h2>
        </template>

        <div class="py-12">
	        <!-- Client Form -->
			<div class="mb-3 p-4 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
				<div class="mx-auto">
					<div class="px-2 mb-3">
						<h2 class="heading">Manage Your Clients</h2>
						<p class="">
							Use the form to create and edit Clients.
						</p>
					</div>
					<div class="px-2 d-md-flex align-items-end mb-3">
						<div class="flex-1 mb-3 mb-md-0 me-md-5">
							<label for="name" class="form-label">Business Name or First Name</label>
							<input
								type="text"
								class="form-control"
								id="name_first"
								placeholder="Acme Inc. or John"
								v-model="state.client.name_first"
								>
						</div>

						<div class="flex-1 mb-3 mb-md-0">
							<label for="name" class="form-label">Last Name</label>
							<input
								type="text"
								class="form-control"
								id="name_last"
								placeholder="Last Name"
								v-model="state.client.name_last"
								>
						</div>
					</div>

					<div class="px-2 d-md-flex align-items-end mb-3">
						<div class="flex-1 mb-3 mb-md-0 me-md-5">
							<label for="name" class="form-label">Email</label>
							<input
								type="email"
								class="form-control"
								id="email"
								placeholder="example@email.com"
								v-model="state.client.email"
								>
						</div>
					</div>

					<div class="px-2 d-md-flex align-items-end mb-5">
						<div class="flex-1 mb-3 mb-md-0">
							<label for="name" class="form-label">Description</label>
							<textarea
								class="form-control"
								rows=5
								cols=40
								id="description"
								placeholder="Description"
								v-model="state.client.description"
								>
							</textarea>
						</div>
					</div>

					<div class="px-2 d-md-flex align-items-end">
						<div class="mb-3 mb-md-0 me-md-5">
							<button
								type="button"
								class="btn btn-success"
								style="min-width:100px;"
								@click="saveClient"
								>
								Save
							</button>
						</div>
						<div class="ms-3 mb-3 mb-md-0">
							<button
								type="button"
								class="btn btn-outline-dark"
								@click="cancelEdit"
								>
									Cancel
								</button>
						</div>
					</div>
				</div>
			</div>

	        <!-- Client List -->
			<div class="mb-3 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
                <div class="list-group">
                	<div v-for="_client in state.clients" :key="_client.id"
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
		                		{{_client.name_first}} {{_client.name_last}}
                			</div>
                			<div class="sub-heading">
                				<div class="mb-1">
	                				{{_client.email}}
								</div>
								<div>
									{{_client.description}}
								</div>
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
                					@click="editClient(_client)"
                					>
                						Edit
            					</button>
                			</div>

                			<!-- Confirm before deleting a Client -->
                			<div class="ln-list-item__action">
                				<button
                					class="btn btn-danger"
                					type="button"
                					@click="deleteClient_init(_client)"
                					>
                						Delete
            					</button>
                			</div>

                		</div>
                	</div>
                </div>
            </div>

			<!-- Confirm Delete Modal -->
			<div class="modal fade" id="modal_client_delete_confirm" tabindex="-1" aria-labelledby="modal_client_delete_confirm_label" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="modal_client_delete_confirm_label">Delete a Client</h5>
			        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			      </div>
			      <div class="modal-body">
			        Are you sure you want to delete this Client?
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteClient">Confirm</button>
			      </div>
			    </div>
			  </div>
			</div>
			<!-- End Confirm Delete Modal -->

        </div>
	</AppLayout>
</template>
