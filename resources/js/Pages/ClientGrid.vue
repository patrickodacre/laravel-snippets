<script setup>
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted, watch, computed } from 'vue'
import Modal from '@/Shared/Modal.vue'
import VPagination from "@hennge/vue3-pagination";
import "@hennge/vue3-pagination/dist/vue3-pagination.css";

const local_storage_column_key = 'ln_client_grid_columns'

const props = defineProps({
	tags: Array,
})

const state = reactive({
	editing_client: null,
	deleting_client: null,

	client: newClient(),
	clients: [],
	tags: [],
	tag_filter_terms: "",
	filtered_tag_chips: [],

	modal_client_form: null,
	modal_client_delete_confirm: null,

	selected_tags: [],
	filters: {
		tags: [],
		search: null,
	},

	columns: {
		name_first: {
			label: "First Name",
			is_visible: true,
		},
		name_last: {
			label: "Last Name",
			is_visible: true,
		},
		email: {
			label: "Email",
			is_visible: true,
		},
		description: {
			label: "Description",
			is_visible: true,
		},
		tags: {
			label: "Tags",
			is_visible: true,
		},
	},

    // pagination
    pagination : {
    	current_page: 1,
    	per_page: 10,
	    total_number_of_pages: 0,
	    range: 5,
    },
})

const tag_filter_options = computed(() => {
	if (! state.tag_filter_terms)
	{
		return props.tags
	}

	const search = state.tag_filter_terms.toLowerCase()

	return props.tags.filter(t => t.name.toLowerCase().indexOf(search) > -1)
})

watch(state.columns, (new_value, old_value) => {
	localStorage.setItem(local_storage_column_key, JSON.stringify(new_value))
})

onMounted(() => {

	getClients()

	state.modal_settings = new bootstrap.Modal('#modal_settings', {})
	state.modal_settings._element.addEventListener('hide.bs.modal', () => {})

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

	// intiialize columns
	let columns = localStorage.getItem(local_storage_column_key)

	if (columns)
	{
		columns = JSON.parse(columns)

		for (const column_name in columns)
		{
			state.columns[column_name] = columns[column_name]
		}
	}
})

function openSettings()
{
	state.modal_settings.show()
}

// start Filter Tags
function updateFilteredTagChips(tag)
{
    let found = false

    // already showing a chip?
    for (let i = 0; i < state.filtered_tag_chips.length; i++)
    {
        if (state.filtered_tag_chips[i].id == tag.id)
        {
            found = true
        }
    }

    if (found) return

    state.filtered_tag_chips.push(JSON.parse(JSON.stringify(tag)))

    getClients()

}

function removeChip(chip)
{
    // remove from chips
    state.filtered_tag_chips = state.filtered_tag_chips
        .filter(c => c.id != chip.id)

    getClients()

}
// end Filter Tags


function getClients(page = state.pagination.current_page)
{
	state.filters.tags = state.filtered_tag_chips.map(tag => tag.id)

	axios.post('/clients/grid-data', {
		filters: state.filters,
		config: {
			per_page: state.pagination.per_page,
		},
		page,
	})
	.then(res => {

		state.pagination.total_number_of_pages = res.data.clients.last_page
		state.pagination.current_page = res.data.clients.current_page
		state.clients = res.data.clients.data
	})
	.catch(err => {
		debugger
	})


}

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

			<!-- Filters -->
			<div class="mb-3 p-4 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
				<div class="row">
					<div class="col-md-6 mb-3 mb-md-0">
						<label for="filters.search" class="form-label">Search</label>
						<input
							@keyup.enter="getClients"
							type="text"
							class="form-control"
							id="filters.search"
							placeholder="Search Name, Email, and Description"
							v-model="state.filters.search"
							>
					</div>
					<div class="col-md-6">

						<!-- Start Filter for Tags -->
						<div class="mb-2 p-0"
							style=" border-bottom: 1px solid #d5d5d5;"
							>
							<input
								class="form-label mb-0"
								style="border: none;"
								type="text"
								placeholder="Filter for Tags"
								v-model="state.tag_filter_terms"
								>
						</div>
	                    <ul
		                    class="list-group mb-5"
	                        v-if="props.tags.length > 0"
	                        >
	                        <li class="list-group-item"
	                        	v-for="tag in tag_filter_options"
	                        	:value="tag.id"
	                        	:key="tag.id"
	                        	@click="updateFilteredTagChips(tag)"
	                    	>
		                        {{ tag.name }}
		                    </li>
	                    </ul>

	                    <div class="">
                            <div class="form-group mb-3 chips">
                                <div class="d-flex align-items-center justify-content-start">
                                    <div
                                        class="ms-1 me-1"
                                        v-for="chip in state.filtered_tag_chips"
                                        :key="chip.id"
                                        >
                                        <button
                                        	class="btn btn-success"
                                            @click="removeChip(chip)"
                                        >
	                                        @{{ chip.name }}
	                                    </button>
                                    </div>
                                </div>
                            </div>

	                    </div>
	                    <!-- End Filter for Tags -->


					</div>
				</div>


				<div class="
					mt-3
					d-flex align-items-center justify-content-end">
					<div class="">
						<button
							class="btn btn-primary"
							@click="getClients"
						>
							Search
						</button>
					</div>
				</div>



			</div>

			<!-- Client Grid -->

			<div class="mb-3 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto" style="overflow-x: scroll;">
				<div class="d-flex align-items-center justify-content-end p-2">
    				<button
    					class="btn btn-outline-secondary bg-white"
    					type="button"
    					@click="openSettings"
    					>
    						Settings
					</button>
				</div>
				<table class="table table-striped">
					<thead>
					    <tr>
						    <th scope="col">Actions</th>
						    <th v-show="state.columns.name_first.is_visible" scope="col">First Name</th>
						    <th v-show="state.columns.name_last.is_visible" scope="col">Last Name</th>
						    <th v-show="state.columns.email.is_visible" scope="col">Email</th>
						    <th v-show="state.columns.description.is_visible" scope="col">Description</th>
						    <th v-show="state.columns.tags.is_visible" scope="col">Tags</th>
					    </tr>
				  </thead>
				  <tbody>
				    <tr v-for="client in state.clients"
				    	:key="client.id"
				    >
				      <th scope="row"
				      	class="d-flex align-items-center"
				      	style="
					      	border-right: 1px solid #d9d9d9
				      	"
				      	>

                			<div class="ln-list-item__action me-2">
                				<button
                					class="btn btn-outline-secondary bg-white"
                					type="button"
                					@click="editClient(client)"
                					>
                						Edit
            					</button>
                			</div>

                			<!-- Confirm before deleting a Client -->
                			<div class="ln-list-item__action">
                				<button
                					class="btn btn-danger"
                					type="button"
                					@click="deleteClient_init(client)"
                					>
                						Delete
            					</button>
                			</div>


				      </th>
				      <td v-show="state.columns.name_first.is_visible" style="">{{ client.name_first }}</td>
				      <td v-show="state.columns.name_last.is_visible" style="">{{ client.name_last }}</td>
				      <td v-show="state.columns.email.is_visible" style="">{{ client.email }}</td>
				      <td v-show="state.columns.description.is_visible" style="">{{ client.description }}</td>
				      <td v-show="state.columns.tags.is_visible" style="">{{ client.tags.map(t => t.name).join(', ') }}</td>
				    </tr>
				  </tbody>
				</table>


			</div>
			<div class="
				mb-3
				bg-white
				shadow
				bg-body
				rounded
				w-75
				ln-max-width
				mx-auto
				p-3
				d-flex
				align-items-center
				justify-content-center
				"
				>
				<v-pagination
			  		v-model="state.pagination.current_page"
				    :pages="state.pagination.total_number_of_pages"
				    :range-size="state.pagination.range"
				    active-color="#DCEDFF"
				    @update:modelValue="getClients"
			  	/>


			</div>


			<!-- Confirm Settings Modal -->
			<Modal id="modal_settings">
				<template #title>
					Settings
				</template>
				<template #body>
					<div
						v-for="(config, column) in state.columns"
						:key="column"
						class="d-flex align-items-center">
							<input
								v-model="config.is_visible"
								:id="column"
								class="me-3"
								type="checkbox"
								/>
							<label :for="column">{{ config.label }}</label>
					</div>


				</template>
				<template #footer>
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
				</template>
			</Modal>
			<!-- End Confirm Settings Modal -->


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
