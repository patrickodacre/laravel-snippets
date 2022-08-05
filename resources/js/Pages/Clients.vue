<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted } from 'vue'
import Modal from '@/Shared/Modal.vue'

const state = reactive({
	editing_client: null,
	deleting_client: null,

	client: newClient(),
	clients: [],

	modal_client_form: null,
	modal_client_delete_confirm: null,

	selected_tags: [],
})

const props = defineProps({
    clients: Array,
    tags: Array,
});

onMounted(() => {
	state.clients = props.clients

	state.modal_client_form = new bootstrap.Modal('#modal_client_form', {})
	state.modal_client_form._element.addEventListener('hide.bs.modal', () => {
		cancelEdit()
	})

	state.modal_client_delete_confirm = new bootstrap.Modal('#modal_client_delete_confirm', {})

	// make sure we clear the client being deleted if we cancel / close the confirm modal
	state.modal_client_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_client = null

		// also clear the form in case we were editing before initializing a delete
		state.client = newClient()
		state.deleting_client = null
	})
})

function newClient_init()
{
	state.client = newClient()
	state.editing_client = null
	state.modal_client_form.show()
}

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
	state.selected_tags = state.client.tags.map(t => t.id)
	state.modal_client_form.show()
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
	state.selected_tags = []
}

function saveClient()
{
	if (state.editing_client && state.editing_client.id)
	{

	    axios.put(
	    	route('clients_update', {client: state.editing_client.id}),
	    	{
	    		name_first: state.client.name_first,
	    		name_last: state.client.name_last,
	    		description: state.client.description,
	    		email: state.client.email,
	    		tags: state.selected_tags,
		    }
	    )
	    .then(res => {
	    	const k = res.data.client

	    	for (let i = 0; i < state.clients.length; i++)
	    	{
	    		if (state.clients[i].id === k.id)
	    		{
	    			state.clients[i] = k
	    		}
	    	}

	    	state.modal_client_form.hide()

	    })
	    .catch(err => {
	    	debugger
	    })

		cancelEdit()
		return
	}

    axios.post(route('clients_store'), {
    	name_first: state.client.name_first,
    	name_last: state.client.name_last,
    	description: state.client.description,
    	email: state.client.email,
    	tags: state.selected_tags,
    })
	    .then(res => {

	    	// clear the form
	    	state.client = newClient()
	    	state.clients.push(res.data.client)

	    	state.modal_client_form.hide()
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
				<div class="
					px-2
					d-md-flex align-items-center justify-content-between">

					<div class="flex-1">
						<h2 class="heading">Manage Your Clients</h2>
						<div class="sub-heading">
							Use the form to create and edit Clients.
						</div>
					</div>
					<div class="ms-auto d-md-flex align-items-center">
						<div class="">
							<button
								type="button"
								class="btn btn-success"
								@click="newClient_init"
							>
								+ New Client
							</button>
						</div>
					</div>

				</div>




				<div class="mx-auto d-lg-flex">

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



			<!-- Start New Client Modal -->
            <Modal id="modal_client_form">
            	<template #title>
		        	<span v-if="state.editing_client && state.editing_client.id">Edit Client</span>
		        	<span v-else>Create Client</span>
            	</template>
            	<template #body>

					<div class="mb-4">
						<label for="name" class="form-label">Business Name or First Name</label>
						<input
							type="text"
							class="form-control"
							id="name_first"
							placeholder="Acme Inc. or John"
							v-model="state.client.name_first"
							>
					</div>

					<div class="mb-4">
						<label for="name" class="form-label">Last Name</label>
						<input
							type="text"
							class="form-control"
							id="name_last"
							placeholder="Last Name"
							v-model="state.client.name_last"
							>
					</div>


					<div class="mb-4">
						<label for="name" class="form-label">Email</label>
						<input
							type="email"
							class="form-control"
							id="email"
							placeholder="example@email.com"
							v-model="state.client.email"
							>
					</div>

					<div class="mb-4">
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


					<!-- Right -->
					<div class="mb-5">

						<!-- Select Tags -->
						<label
							for="select_tags"
							class="col-form-label form-check-label"
							>
                            Select Tags
                        </label>
                        <select
                            id="select_tags"
                            name="select_tags"
                            v-if="props.tags.length > 0"

							multiple
                            v-model="state.selected_tags"

                            class="form-select w-full bg-white border border-gray-200 px-3 py-2 rounded outline-none">
	                        <option
	                        	v-for="tag in props.tags"
	                        	:value="tag.id"
	                        	:key="tag.id"
                        	>
		                        {{ tag.name }}
		                    </option>
	                    </select>
	                    <div
		                    v-else
		                    class=""
		                    >
	                    	You don't have any tags.
	                    </div>

					</div>



            	</template>

            	<template #footer>
				    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="saveClient">
			        	<span v-if="state.editing_client && state.editing_client.id">Edit</span>
			        	<span v-else>Create</span>
			        </button>

            	</template>
            </Modal>
			<!-- End New Client Modal -->

			<!-- Confirm Delete Modal -->
			<Modal id="modal_client_delete_confirm">
				<template #title>
					Delete a Client
				</template>
				<template #body>
			        Are you sure you want to delete this Client?
				</template>
				<template #footer>
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-success" @click="deleteClient">Confirm</button>
				</template>
			</Modal>
			<!-- End Confirm Delete Modal -->

        </div>
	</AppLayout>
</template>
