<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted } from 'vue'
import Modal from "@/Shared/Modal.vue"

const props = defineProps({
	// tags are nested in categories
    categories: Array,
    types: Array,
});

const state = reactive({
	categories: [],

	category: newCategory(),
	editing_category: null,
	deleting_category: null,
	viewing_category: null,

	tag: newTag(),
	editing_tag: null,
	deleting_tag: null,

	modal_category_form: null,
	modal_category_delete_confirm: null,

	modal_tag_form: null,
	modal_tag_delete_confirm: null,
})

onMounted(() => {
	state.categories = props.categories

	// Create / Edit
	state.modal_category_form = new bootstrap.Modal('#modal_category_form', {})
	state.modal_category_form._element.addEventListener('hide.bs.modal', () => {
		cancelEditCategory()
	})

	state.modal_tag_form = new bootstrap.Modal('#modal_tag_form', {})
	state.modal_tag_form._element.addEventListener('hide.bs.modal', () => {
		cancelEditTag()
	})

	// Delete
	state.modal_category_delete_confirm = new bootstrap.Modal('#modal_category_delete_confirm', {})
	state.modal_category_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_category = null
		state.category = newCategory()
		state.deleting_category = null
	})

	state.modal_tag_delete_confirm = new bootstrap.Modal('#modal_tag_delete_confirm', {})
	state.modal_tag_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_tag = null
		state.tag = newTag()
		state.deleting_tag = null
	})

})

/* ======================
	Categories
====================== */

function newCategory_init()
{
	state.category = newCategory()
	state.editing_category = null
	state.modal_category_form.show()
}

function newCategory()
{
	return {
		type_id: null,
		name: null,
		description: null,
	}
}

function editCategory(_category)
{
	// avoid mutating by reference
	state.category = JSON.parse(JSON.stringify(_category))
	// here we WANT to mutate by reference
	// so our tags list isn't reset when we do things
	// like EDIT category after adding new tags
	state.editing_category = _category

	state.modal_category_form.show()
}

function cancelEditCategory()
{
	state.editing_category = null
	state.category = newCategory()
}

function deleteCategory_init(category)
{
	state.editing_category = null
	state.viewing_category = null
	state.deleting_category = category

	state.modal_category_delete_confirm.show()
}


function deleteCategory()
{

	axios.delete(route('category_delete', {category: state.deleting_category.id}))
		.then(res => {

			state.categories = state.categories.filter(k => k.id != state.deleting_category.id)

			state.deleting_category = null

			state.modal_category_delete_confirm.hide()

		})
		.catch(err => {
			debugger
		})
}

function saveCategory()
{
	const {name, description, type_id} = state.category

	if (state.editing_category && state.editing_category.id)
	{
	    axios.put(route('category_update', {category: state.editing_category.id}), {
	    	name,
	    	description,
	    	type_id,
	    })
	    .then(res => {
	    	const c = res.data.category

	    	for (let i = 0; i < state.categories.length; i++)
	    	{
	    		if (state.categories[i].id === c.id)
	    		{
	    			state.categories[i] = {...state.categories[i], ...c}
	    		}
	    	}

	    	state.modal_category_form.hide()

	    })
	    .catch(err => {
	    	debugger
	    })

		cancelEditCategory()
		return
	}

    axios.post(route('category_create', {
    	name,
    	description,
    	type_id,
    }))
	    .then(res => {

	    	// clear the form
	    	state.category = newCategory()
	    	state.categories.push(res.data.category)

	    	state.modal_category_form.hide()
	    })
	    .catch(err => {
	    	debugger
	    })

}



/* ======================
	Tags
====================== */

function toggleViewTags(_category)
{
	if (! state.viewing_category)
	{
		state.viewing_category = _category
	}
	// changing to another category
	else if (state.viewing_category.id !== _category.id)
	{
		state.viewing_category = _category
	}
	else
	{
		state.viewing_category = null
	}
}

function newTag_init()
{
	state.tag = newTag()
	state.editing_tag = null
	state.modal_tag_form.show()
}

function newTag()
{
	return {
		name: null,
		description: null,
		category_id: null,
	}
}

function editTag(_tag)
{
	// avoid mutating by reference
	state.tag = JSON.parse(JSON.stringify(_tag))
	state.editing_tag = _tag

	state.modal_tag_form.show()
}

function cancelEditTag()
{
	state.editing_tag = null
	state.tag = newTag()
}


function deleteTag_init(tag)
{
	// NOTE:: do not reset editing_category
	// it keeps the tags displayed properly
	state.editing_tag = null
	state.deleting_tag = tag

	state.modal_tag_delete_confirm.show()
}

function deleteTag()
{

	axios.delete(route('tag_delete', {tag: state.deleting_tag.id}))
		.then(res => {

			state.viewing_category.tags = state.viewing_category.tags
				.filter(t => t.id != state.deleting_tag.id)

			state.deleting_tag = null

			state.modal_tag_delete_confirm.hide()

		})
		.catch(err => {
			debugger
		})
}

function saveTag()
{

	const {name, description} = state.tag

	if (!state.viewing_category || !state.viewing_category.id)
	{
		return
	}

	if (state.editing_tag && state.editing_tag.id)
	{

	    return axios.put(route('tag_update', {tag: state.editing_tag.id}), {
	    	name,
	    	description,
	    })
	    .then(res => {
	    	const c = res.data.tag

	    	for (let i = 0; i < state.viewing_category.tags.length; i++)
	    	{
	    		if (state.viewing_category.tags[i].id === c.id)
	    		{
	    			state.viewing_category.tags[i] = c
	    		}
	    	}

	    	state.modal_tag_form.hide()
	    })
	    .catch(err => {
	    	debugger
	    })

	}

    axios.post(route('tag_create', {
    	name,
    	description,
    	category_id: state.viewing_category.id,
    }))
	    .then(res => {

	    	// clear the form
	    	state.tag = newTag()
	    	state.viewing_category.tags.push(res.data.tag)

	    	state.modal_tag_form.hide()
	    })
	    .catch(err => {
	    	debugger
	    })


}

</script>

<template>
    <AppLayout title="tags">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tags
            </h2>
        </template>

        <div class="py-12">
			<div class="mb-3 p-4 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">

				<div class="
					px-2
					d-md-flex align-items-center justify-content-between">

					<div class="flex-1">
						<div class="heading">Manage Your Tags</div>
						<div class="sub-heading">
							Use the form to create and edit Tags and Tag Categories.
						</div>
					</div>
					<div class="ms-auto d-md-flex align-items-center">
						<div class="">
							<button
								type="button"
								class="btn btn-success"
								@click="newCategory_init"
							>
								+ New Category
							</button>
						</div>
					</div>

				</div>

				<div class="mx-auto mb-3">

					<div class="px-2 mb-3">
					</div>

				</div>

			</div>


	        <!-- Category List -->
			<div class="mb-3 bg-white shadow bg-body rounded w-75 ln-max-width mx-auto">
	            <div class="list-group">
	            	<div v-for="_category in state.categories"
	                	:key="_category.id"
	                	class="list-group-item py-3"
	            	>
		        		<div class="ln-list-item__top"
		                	style="
			                	display: flex;
			                	align-items: center;
			                	justify-content: space-between;
		                	"
	                	>
		                	<!-- LEFT section -->
	                		<div class="ln-list-item__left">
	                			<div class="heading">
			                		{{_category.name}}
	                			</div>
	                			<div class="sub-heading">
	                				{{ _category.description }}
	                			</div>
	                		</div>

	                		<!-- RIGHT section -->
	                		<div class="ln-list-item__right">

	                			<!-- Actions are laid our horizontally -->
	                			<!-- All buttons are wrapped in divs to make sure FLEX works consistently across all browsers -->
	                			<div class="ln-list-item__action me-2">
	                				<button
	                					class="btn"
	                					:class="{
	                						'btn-secondary': state.viewing_category && (state.viewing_category.id === _category.id),
	                						'btn-outline-secondary': ! state.viewing_category || (state.viewing_category.id !== _category.id),
	                					}"
	                					type="button"
	                					@click="toggleViewTags(_category)"
	                					>
	                						<span v-if="! state.viewing_category || (state.viewing_category.id !== _category.id)">View Tags</span>
	                						<span v-else>Hide Tags</span>
	            					</button>
	                			</div>

	                			<div class="ln-list-item__action me-2">
	                				<button
	                					class="btn btn-outline-secondary"
	                					type="button"
	                					@click="editCategory(_category)"
	                					>
	                						Edit
	            					</button>
	                			</div>

	                			<!-- Confirm before deleting a Category -->
	                			<div class="ln-list-item__action">
	                				<button
	                					class="btn btn-danger"
	                					type="button"
	                					@click="deleteCategory_init(_category)"
	                					>
	                						Delete
	            					</button>
	                			</div>

	                		</div>

						</div>
						<!-- Nested List -->
						<div class="ln-list-item__bottom"
						>
							<template
								v-if="
									state.viewing_category
									&& state.viewing_category.id == _category.id
								"
							>

								<div class="list-group my-5">
									<div class="mb-5 d-flex align-items-center justify-content-between">
										<div class="ms-auto d-flex">
											<div class="list-group-heading__action">
												<button
													type="button"
													class="btn btn-success"
													@click="newTag_init"
												>
													+ New Tag
												</button>
											</div>
										</div>
									</div>

									<div v-for="_tag in state.viewing_category.tags"
					                	:key="_tag.id"
					                	class="list-group-item ps-5 py-3"
					                	style="
						                	display: flex;
						                	align-items: center;
						                	justify-content: space-between;
										 	background-color: #f8f8f8;
					                	"
				                	>

					                	<!-- LEFT section -->
				                		<div class="ln-list-item__left">
				                			<div class="heading">
						                		{{_tag.name}}
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
				                					@click="editTag(_tag)"
				                					>
				                						Edit
				            					</button>
				                			</div>

				                			<!-- Confirm before deleting a Category -->
				                			<div class="ln-list-item__action">
				                				<button
				                					class="btn btn-danger"
				                					type="button"
				                					@click="deleteTag_init(_tag)"
				                					>
				                						Delete
				            					</button>
				                			</div>

				                		</div>
			                		</div>

								</div>
							</template>
						</div>
                	</div>
                </div>
            </div>

			<!-- Start New Category Modal -->
            <Modal id="modal_category_form">
            	<template #title>
		        	<span v-if="state.editing_category && state.editing_category.id">Edit Category</span>
		        	<span v-else>Create Category</span>
            	</template>
            	<template #body>

					<!-- Start Category Form -->
					<div class="mb-3">
						<label
							for="select_tag_type"
							class="form-label"
							>
		                    Select Category Type
		                </label>
		                <select
		                    id="select_tag_type"
		                    name="select_tag_type"

		                    v-model="state.category.type_id"

		                    :disabled="state.editing_category && state.editing_category.id"
		                    class="form-select"
		                >
		                    <option
		                    	v-for="tag_type in props.types"
		                    	:value="tag_type.id"
		                    	:key="tag_type.id"
		                	>
		                		{{ tag_type.name }}
		                	</option>
		                </select>

					</div>

					<div class="mb-3">
						<label for="name" class="form-label">Category Name</label>
						<input
							id="name"
							type="text"
							class="form-control"
							v-model="state.category.name"
							>
					</div>

					<div class="mb-3">
						<label for="description" class="form-label">Description</label>
						<input
							id="description"
							type="text"
							class="form-control"
							v-model="state.category.description"
							>
					</div>
					<!-- End Category Form -->

            	</template>

            	<template #footer>
				    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="saveCategory">
			        	<span v-if="state.editing_category && state.editing_category.id">Edit</span>
			        	<span v-else>Create</span>
			        </button>

            	</template>
            </Modal>
			<!-- End New Category Modal -->


			<!-- New Tag Modal -->
            <Modal id="modal_tag_form">
            	<template #title>
		        	<span v-if="state.editing_tag && state.editing_tag.id">Edit Tag</span>
		        	<span v-else>Create Tag</span>
            	</template>
            	<template #body>
            		<!-- Start Tag Form -->
					<div class="mb-3">
						<label for="name" class="form-label">Tag Name</label>
						<input
							id="name"
							type="text"
							class="form-control"
							v-model="state.tag.name"
							>
					</div>

					<div class="mb-3">
						<label for="description" class="form-label">Description</label>
						<input
							id="description"
							type="text"
							class="form-control"
							v-model="state.tag.description"
							>
					</div>
					<!-- End Tag Form -->

            	</template>
            	<template #footer>
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="saveTag">
			        	<span v-if="state.editing_tag && state.editing_tag.id">Edit</span>
			        	<span v-else>Create</span>
			        </button>

            	</template>
            </Modal>
			<!-- End New Tag Modal -->


			<!-- Confirm Delete Modal -->
			<Modal id="modal_category_delete_confirm">
				<template #title>
			        Delete a Category
				</template>
				<template #body>
			        Are you sure you want to delete this Tag Category?
				</template>
				<template #footer>
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteCategory">Confirm</button>
				</template>
			</Modal>
			<!-- End Confirm Delete Modal -->

			<!-- Confirm Delete Modal -->
			<Modal id="modal_tag_delete_confirm">
				<template #title>Delete a Tag</template>
				<template #body>
					Are you sure you want to delete this Tag?
				</template>
				<template #footer>
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteTag">Confirm</button>
				</template>
			</Modal>
			<!-- End Confirm Delete Modal -->

        </div>
	</AppLayout>
</template>
