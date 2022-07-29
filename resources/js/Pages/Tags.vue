<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted } from 'vue'

const state = reactive({
	category: newCategory(),
	categories: [],
	editing_category: null,
	deleting_category: null,

	tag: newTag(),
	editing_tag: null,
	deleting_tag: null,
	deleting_tag_from_category: null,

	modal_category_delete_confirm: null,
	modal_tag_delete_confirm: null,
})

const props = defineProps({
	// tags are nested in categories
    categories: Array,
});

onMounted(() => {
	state.categories = props.categories

	state.modal_category_delete_confirm = new bootstrap.Modal('#modal_category_delete_confirm', {})
	state.modal_tag_delete_confirm = new bootstrap.Modal('#modal_tag_delete_confirm', {})

	state.modal_category_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_category = null
		state.category = newCategory()
		state.deleting_category = null
	})

	state.modal_tag_delete_confirm._element.addEventListener('hide.bs.modal', () => {
		state.editing_tag = null
		state.tag = newTag()
		state.deleting_tag = null
	})

})

function newTag()
{
	return {
		name: null,
		description: null,
		category_id: null,
	}
}

function newCategory()
{
	return {
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
}

function editTag(_tag)
{
	// avoid mutating by reference
	state.editing_tag = JSON.parse(JSON.stringify(_tag))
	state.tag = JSON.parse(JSON.stringify(_tag))
}

function cancelEditCategory()
{
	state.editing_category = null
	state.category = newCategory()
	state.tag = newTag()
}

function cancelEditTag()
{
	state.editing_tag = null
	state.tag = newTag()
}



function deleteCategory_init(category)
{
	state.editing_category = null
	state.deleting_category = category

	state.modal_category_delete_confirm.show()
}

function deleteTag_init(tag)
{
	// NOTE:: do not reset editing_category
	// it keeps the tags displayed properly
	state.editing_tag = null
	state.deleting_tag_from_category = state.editing_category
	state.deleting_tag = tag

	state.modal_tag_delete_confirm.show()
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

function deleteTag()
{

	axios.delete(route('tag_delete', {tag: state.deleting_tag.id}))
		.then(res => {

			state.editing_category.tags = state.editing_category.tags
				.filter(t => t.id != state.deleting_tag.id)

			state.deleting_tag = null
			state.deleting_tag_from_category = null

			state.modal_tag_delete_confirm.hide()

		})
		.catch(err => {
			debugger
		})
}

function saveCategory()
{
	if (state.editing_category && state.editing_category.id)
	{

	    axios.put(route('category_update', {category: state.editing_category.id}), {
	    	name: state.category.name,
	    	description: state.category.description,
	    })
	    .then(res => {
	    	const c = res.data.category

	    	for (let i = 0; i < state.categories.length; i++)
	    	{
	    		if (state.categories[i].id === c.id)
	    		{
	    			state.categories[i] = c
	    		}
	    	}

	    })
	    .catch(err => {
	    	debugger
	    })

		cancelEditCategory()
		return
	}

    axios.post(route('category_create', {
    	name: state.category.name,
    	description: state.category.description,
    }))
	    .then(res => {

	    	// clear the form
	    	state.category = newCategory()
	    	state.categories.push(res.data.category)
	    })
	    .catch(err => {
	    	debugger
	    })

}

function saveTag()
{
	if (!state.editing_category || !state.editing_category.id)
	{
		return
	}

	if (state.editing_tag && state.editing_tag.id)
	{

	    return axios.put(route('tag_update', {tag: state.editing_tag.id}), {
	    	name: state.tag.name,
	    	description: state.tag.description,
	    })
	    .then(res => {
	    	const c = res.data.tag

	    	for (let i = 0; i < state.editing_category.tags.length; i++)
	    	{
	    		if (state.editing_category.tags[i].id === c.id)
	    		{
	    			state.editing_category.tags[i] = c
	    		}
	    	}

	    })
	    .catch(err => {
	    	debugger
	    })

	}

    axios.post(route('tag_create', {
    	name: state.tag.name,
    	description: state.tag.description,
    	category_id: state.editing_category.id,
    }))
	    .then(res => {

	    	// clear the form
	    	state.tag = newTag()
	    	state.editing_category.tags.push(res.data.tag)
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
				<div class="mx-auto mb-3">
					<div class="px-2 mb-3">
						<h2 class="heading">Manage Your Tags</h2>
						<p class="">
							Use the form to create and edit Tags and Tag Categories.
						</p>
					</div>

					<!-- Start Category Form -->
					<div class="px-2 d-md-flex align-items-end mb-3">
						<div class="flex-1 mb-3 mb-md-0 me-md-5">
							<label for="name" class="form-label">Category Name</label>
							<input
								id="name"
								type="text"
								class="form-control"
								placeholder="Technologies"
								v-model="state.category.name"
								>
						</div>

						<div class="flex-1 mb-3 mb-md-0">
							<label for="description" class="form-label">Description</label>
							<input
								id="description"
								type="text"
								class="form-control"
								v-model="state.category.description"
								>
						</div>

						<div class="ms-auto mb-3 mb-md-0 me-md-3">
							<button
								type="button"
								class="btn btn-success"
								style="min-width:100px;"
								@click="saveCategory"
								>
								Save Category
							</button>
						</div>
						<div class="ms-3 mb-3 mb-md-0">
							<button
								type="button"
								class="btn btn-outline-dark"
								@click="cancelEditCategory"
								>
									Cancel / Clear Form
								</button>
						</div>
					</div>
					<!-- End Category Form -->

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
		        			:class="{is_editing: state.editing_category && _category.id == state.editing_category.id}"
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
	                		</div>

	                		<!-- RIGHT section -->
	                		<div class="ln-list-item__right">

	                			<!-- Actions are laid our horizontally -->
	                			<!-- All buttons are wrapped in divs to make sure FLEX works consistently across all browsers -->
	                			<div class="ln-list-item__action me-2">
	                				<button
	                					class="btn btn-outline-secondary"
	                					type="button"
	                					@click="editCategory(_category)"
	                					>
	                						Edit
	            					</button>
	                			</div>

	                			<div
	                				v-if="state.editing_category
		                				&& state.editing_category.id == _category.id"
	                				class="ln-list-item__action me-2"
	                			>
	                				<button
	                					class="btn btn-dark"
	                					type="button"
	                					@click="cancelEditCategory"
	                					>
	                						X Cancel X
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
									state.editing_category
									&& state.editing_category.id == _category.id
								"
							>
								<!-- Start Tag Form -->
								<div class="px-2 d-md-flex align-items-end mb-3" v-if="state.editing_category && state.editing_category.id">
									<div class="flex-1 mb-3 mb-md-0 me-md-5">
										<label for="name" class="form-label">Tag Name</label>
										<input
											id="name"
											type="text"
											class="form-control"
											placeholder="Technologies"
											v-model="state.tag.name"
											>
									</div>

									<div class="flex-1 mb-3 mb-md-0">
										<label for="description" class="form-label">Description</label>
										<input
											id="description"
											type="text"
											class="form-control"
											v-model="state.tag.description"
											>
									</div>

									<div class="ms-auto mb-3 mb-md-0 me-md-3">
										<button
											type="button"
											class="btn btn-success"
											style="min-width:100px;"
											@click="saveTag"
											>
											Save Tag
										</button>
									</div>
									<div class="ms-3 mb-3 mb-md-0">
										<button
											type="button"
											class="btn btn-outline-dark"
											@click="cancelEditTag"
											>
												Cancel / Clear Form
											</button>
									</div>
								</div>
								<!-- End Tag Form -->
							</template>

							<template
								v-if="
									state.editing_category
									&& state.editing_category.id == _category.id
									&& state.editing_category.tags
									&& state.editing_category.tags.length > 0
								"
							>

								<div class="list-group my-5">
									<div v-for="_tag in state.editing_category.tags"
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

			<!-- Confirm Delete Modal -->
			<div class="modal fade" id="modal_category_delete_confirm" tabindex="-1" aria-labelledby="modalcategory_delete_confirm_label" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="modal_category_delete_confirm_label">Delete a Category</h5>
			        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			      </div>
			      <div class="modal-body">
			        Are you sure you want to delete this Tag Category?
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteCategory">Confirm</button>
			      </div>
			    </div>
			  </div>
			</div>
			<!-- End Confirm Delete Modal -->

			<!-- Confirm Delete Modal -->
			<div class="modal fade" id="modal_tag_delete_confirm" tabindex="-1" aria-labelledby="modaltag_delete_confirm_label" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="modal_tag_delete_confirm_label">Delete a Tag</h5>
			        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			      </div>
			      <div class="modal-body">
			        Are you sure you want to delete this Tag?
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-success" @click="deleteTag">Confirm</button>
			      </div>
			    </div>
			  </div>
			</div>
			<!-- End Confirm Delete Modal -->

        </div>
	</AppLayout>
</template>
