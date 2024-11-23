<template>
  <q-page class="q-pa-md">
    <div class="col-12 q-mt-md">
      <q-card :class="{ 'bg-dark': $q.dark.isActive }">
        <q-card-section>
          <div class="text-h4" :class="{ 'text-white': $q.dark.isActive }">Product Management</div>
          <q-breadcrumbs class="q-mt-md" :class="{ 'text-white': $q.dark.isActive }">
          </q-breadcrumbs>
        </q-card-section>

        <q-card-section>
          <div class="q-gutter-md row items-center">
            <q-btn label="Add" icon="add" color="orange" class="q-ml-md q-ml-auto" @click="addProduct" />
            <q-input
              v-model="searchQuery"
              placeholder="Search"
              filled
              :dark="$q.dark.isActive"
              :class="{ 'dark-input': $q.dark.isActive }"
            >
              <template v-slot:append>
                <q-icon name="search" :class="{ 'text-white': $q.dark.isActive }" />
              </template>
            </q-input>
          </div>
        </q-card-section>

        <q-table
          :rows="filteredProducts"
          :columns="columns"
          row-key="product_id"
          :pagination.sync="pagination"
          :rows-per-page-options="[10, 20, 30, 50, 0]"
          :loading="loading"
          :dark="$q.dark.isActive"
          class="my-custom-table"
          :class="{ 'dark-table': $q.dark.isActive }"
        >
          <template v-slot:body-cell-category_name="props">
            <q-td>
              {{ props.value }}
            </q-td>
          </template>
          <template v-slot:body-cell-product_description="props">
        <q-td>
          <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ props.row.product_description }}
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-product_image="props">
            <q-td>
              <q-img
                :src="props.row.product_image"
                style="width: 50px; height: 50px; object-fit: cover;"
                 @error="onImageError(props.row)"
              >
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">
                    No image
                  </div>
                </template>
              </q-img>
              <div v-if="props.row.debug_image_path">Debug path: {{ props.row.debug_image_path }}</div>
              <div v-if="props.row.image_error" class="text-negative">Error: {{ props.row.image_error }}</div>
            </q-td>
          </template>
          <template v-slot:body-cell-product_status="props">
            <q-td>
              <q-chip
                :color="props.row.product_status.toLowerCase() === 'available' ? 'green' : 'red'"
                text-color="white"
                square
              >
                {{ props.row.product_status }}
              </q-chip>
            </q-td>
          </template>
          <template v-slot:body-cell-action="props">
            <q-td :props="props" class="text-center">
              <q-btn
              color="orange"
              icon="edit"
              flat
              dense
              @click="editProduct(props.row)"
              />
              <q-btn
                color="red"
                icon="delete"
                flat
                dense
                @click="confirmDelete(props.row)"
              />
            </q-td>
          </template>
          <!-- <template v-slot:body-cell-product_description="props">
            <q-td>
              <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ props.row.description }}
              </div>
            </q-td>
          </template> -->

          <template v-slot:bottom="props">
            <div class="row full-width">
              <div class="col-12 col-sm-6">
                <span>
                  Showing {{ props.pagination.rowsPerPage * (props.pagination.page - 1) + 1 }}
                  to {{ Math.min(props.pagination.rowsPerPage * props.pagination.page, props.pagination.rowsNumber) }}
                  of {{ props.pagination.rowsNumber }} entries
                </span>
              </div>
              <div class="col-12 col-sm-6 text-right">
                <q-pagination
                  v-model="props.pagination.page"
                  :max="props.pagination.pagesNumber"
                  :max-pages="6"
                  :boundary-links="true"
                />
              </div>
            </div>
          </template>
        </q-table>

        <q-card-section class="q-pa-none q-pt-md">

        </q-card-section>
      </q-card>
    </div>

    <!-- Dialog for Add/Edit -->
    <q-dialog v-model="dialogVisible">
      <q-card :class="{ 'bg-dark': $q.dark.isActive }">
        <q-card-section>
          <div class="text-h6" :class="{ 'text-white': $q.dark.isActive }">
            {{ isEdit ? 'Edit Product' : 'Add New Product' }}
          </div>
        </q-card-section>

        <q-card-section>
          <q-select
            v-model="product.category_id"
            :options="categoryOptions"
            option-value="value"
            option-label="label"
            label="Category"
            outlined
            :dark="$q.dark.isActive"
            class="q-mb-md"
            required
          />

          <q-input
            v-model="product.product_name"
            label="Product Name"
            outlined
            dense
            class="q-mb-md"
            :dark="$q.dark.isActive"
            required
          />

          <q-input v-model="product.product_description" label="Description" type="textarea" outlined dense class="q-mb-md" />
          <q-input v-model.number="product.product_price" label="Price" type="number" outlined dense class="q-mb-md" :rules="[val => !!val || 'Product price is required']" required />
          <q-select
            v-model="product.product_status"
            :options="['Available', 'Not Available']"
            label="Status"
            outlined
            dense
            class="q-mb-md"
          />
          <q-file
            v-model="product.product_image"
            label="Product Image"
            outlined
            dense
            accept="image/*"
            class="q-mb-md"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
          </q-file>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="saveProduct" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete confirmation dialog -->
    <q-dialog v-model="deleteDialogVisible" persistent>
      <q-card :class="{ 'bg-dark': $q.dark.isActive }">
        <q-card-section class="row items-center">
          <span class="q-ml-sm" :class="{ 'text-white': $q.dark.isActive }">
            Are you sure you want to delete this product?
          </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="red" @click="deleteProduct" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      category_id: null,
      loading: true,
      searchQuery: '',
      rowsPerPage: 10,
      page: 1,
      columns: [
        { name: 'product_id', label: 'ID', align: 'left', field: 'product_id' },
        {
          name: 'category_name',
          label: 'Category Name',
          align: 'left',
          field: row => this.getCategoryName(row.category_id)
        },
        { name: 'product_name', label: 'Product Name', align: 'left', field: 'product_name' },
        { name: 'product_description', label: 'Descriptions', align: 'left', field: 'product_description' },
        { name: 'product_price', label: 'Product Price', align: 'right', field: 'product_price' },
        { name: 'product_status', label: 'Status', align: 'center', field: 'product_status' },
        { name: 'product_image', label: 'Image', align: 'center', field: 'product_image' },
        { name: 'action', label: 'Action', align: 'center', field: 'action' }
      ],
      products: [],
      categoryOptions: [],
      dialogVisible: false,
      isEdit: false,
      // editingProduct: null

      product: {
        product_id: null,
        category_id: null,
        product_name: '',
        product_description: '',
        product_price: '',
        product_status: 'Available',
        product_image: null
      },
      isMounted: true,
      pagination: {
        sortBy: 'product_id',
        descending: false,
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0
      },
      deleteDialogVisible: false,
      productToDelete: null,
    };
  },
  computed: {
    filteredProducts() {
      const filtered = this.products.filter((product) =>
        product.product_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
      this.pagination.rowsNumber = filtered.length;
      return filtered;
    },
    maxPages() {
      return Math.ceil(this.products.length / this.rowsPerPage);
    },
    selectedCategoryName() {
      const selectedCategory = this.categoryOptions.find(
        option => option.value === this.product.category_id
      );
      return selectedCategory ? selectedCategory.label : '';
    }
  },
  methods: {
    async fetchProducts() {
      try {
        this.loading = true;
        const [productsResponse, categoriesResponse] = await Promise.all([
          axios.get('http://localhost/raj-express/backend/controller/pos_product.php'),
          axios.get('http://localhost/raj-express/backend/controller/pos_categories.php')
        ]);

        if (this.isMounted) {
          this.products = productsResponse.data.map(product => ({
            ...product,
            category_id: product.category_id ? Number(product.category_id) : null,
            product_description: product.product_description || 'No description'
          })).sort((a, b) => a.product_id - b.product_id);

          if (categoriesResponse.data && Array.isArray(categoriesResponse.data.categories)) {
            this.categoryOptions = categoriesResponse.data.categories.map(category => ({
              value: Number(category.category_id),
              label: category.category_name
            }));
          }

          this.pagination.rowsNumber = this.products.length;
        }
      } catch (error) {
        console.error('Error fetching data:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error fetching products',
          position: 'top',
          timeout: 2000
        });
      } finally {
        this.loading = false;
      }
    },
    async saveProduct() {
      console.log('Product object before saving:', JSON.stringify(this.product, null, 2));

      const formData = new FormData();
      for (const key in this.product) {
        if (this.product[key] !== null && this.product[key] !== undefined && this.product[key] !== '') {
          formData.append(key, this.product[key]);
          console.log(`Appending to FormData: ${key} = ${this.product[key]}`);
        } else {
          console.log(`Skipping empty value for: ${key}`);
        }
      }

      formData.append('is_edit', this.isEdit ? '1' : '0');

      console.log('FormData contents:');
      for (let [key, value] of formData.entries()) {
        console.log(key, value);
      }

      try {
        const response = await axios.post('http://localhost/raj-express/backend/controller/pos_product.php', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        console.log('Server response:', response.data);
        if (this.isMounted) {
          await this.fetchProducts();
          this.closeDialog();
          this.$q.notify({
            type: 'positive',
            message: `Product ${this.isEdit ? 'updated' : 'added'} successfully`,
            position: 'top',
            timeout: 2000
          });
        }
      } catch (error) {
        console.error('Error saving product:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error saving product. Please check console for details.',
          position: 'top',
          timeout: 2000
        });
      }
    },
    getCategoryName(categoryId) {
      console.log('Getting category name for ID:', categoryId, 'Type:', typeof categoryId);
      if (categoryId === undefined || categoryId === null) {
        console.warn('Category ID is undefined or null');
        return 'No Category';
      }
      const category = this.categoryOptions.find(cat => cat.value === Number(categoryId));
      if (category) {
        return category.label;
      } else {
        console.warn(`Category not found for ID: ${categoryId}`);
        return 'Unknown Category';
      }
    },
    openDialog(product = null) {
      if (product) {
        this.isEdit = true;
        this.product = { ...product };
      } else {
        this.isEdit = false;
        this.resetForm();
      }
      this.dialogVisible = true;
    },
    closeDialog() {
      this.dialogVisible = false;
    },
    resetForm() {
      this.product = {
        product_id: '',
        category_id: '',
        product_name: '',
        product_description: '',
        product_price: '',
        product_status: 'Available',
        product_image: ''
      };
    },

    addProduct() {
      this.$router.push('/products/add');
    },
    editProduct(product) {
      this.isEdit = true;
      this.product = { ...product };
      this.dialogVisible = true;
      console.log('Editing product:', this.product);
    },
    handleImageError(err) {
      console.error('Error loading image:', err);
    },
    onImageError(product) {
      product.image_error = 'Image not found';
    },
    confirmDelete(product) {
      this.productToDelete = product;
      this.deleteDialogVisible = true;
    },

    async deleteProduct() {
      if (!this.productToDelete) return;

      try {
        const data = {
          product_id: this.productToDelete.product_id
        }
        const response = await axios.post('http://localhost/raj-express/backend/controller/deleteProduct.php', data);

        // Refresh the products list immediately after successful deletion
        await this.fetchProducts();

        this.$q.notify({
          type: 'positive',
          message: 'Product deleted successfully',
          position: 'top',
          timeout: 2000
        });
      } catch (error) {
        console.error('Error deleting product:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Failed to delete product',
          position: 'top',
          timeout: 2000
        });
      } finally {
        this.productToDelete = null;
        this.deleteDialogVisible = false;
      }
    },
  },
  created() {
    this.fetchProducts();
  },
  beforeUnmount() {
    this.isMounted = false;
  }
};
</script>

<style>
.q-td {
  text-align: center;
}

.my-custom-table {
  transition: all 0.3s ease;
}

.dark-table {
  background-color: #1d1d1d !important;
  color: white !important;
}

.dark-table .q-table__top,
.dark-table .q-table__bottom,
.dark-table thead tr:first-child th {
  background-color: #1d1d1d !important;
  color: white !important;
}

.dark-table tbody tr:hover {
  background-color: #2d2d2d !important;
}

.dark-input .q-field__native,
.dark-input .q-field__prefix,
.dark-input .q-field__suffix,
.dark-input .q-field__input {
  color: white !important;
}

.dark-input.q-field--filled .q-field__control {
  background: #2d2d2d !important;
}

/* Add transitions for smooth theme switching */
.q-card,
.q-table,
.q-input,
.q-select,
.text-h4,
.text-h6,
.q-field__label,
.q-field__native,
.q-field__input {
  transition: all 0.3s ease;
}

/* Preserve existing table styles */
.my-custom-table thead tr th {
  position: sticky;
  z-index: 1;
}

.my-custom-table thead tr:first-child th {
  top: 0;
}

.my-custom-table th:first-child,
.my-custom-table td:first-child {
  padding-left: 16px;
}

.my-custom-table th:last-child,
.my-custom-table td:last-child {
  padding-right: 16px;
}

/* Aligning content */
.my-custom-table td:nth-child(1),
.my-custom-table th:nth-child(1),
.my-custom-table td:nth-child(2),
.my-custom-table th:nth-child(2),
.my-custom-table td:nth-child(3),
.my-custom-table th:nth-child(3) {
  text-align: left;
}
</style>
