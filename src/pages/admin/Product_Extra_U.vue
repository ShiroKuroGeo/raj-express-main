<template>
  <q-page class="q-pa-md">
    <div class="col-12 q-mt-md">
      <q-card>
        <q-card-section>
          <div class="text-h4">Add-Ons Management</div>
        </q-card-section>

        <q-card-section>
          <div class="q-gutter-md row items-center">
            <q-btn label="Add" icon="add" color="orange" class="q-ml-md q-ml-auto" @click="openDialog()" />
            <q-input v-model="searchQuery" placeholder="Search" filled>
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
        </q-card-section>

        <q-card-section>
          <q-table :rows="filteredAddsOns" :columns="columns" row-key="addOn_id" :rows-per-page-options="[10]" @request="onRequest" dense>
            <template v-slot:body-cell-action="props">
              <q-td :props="props" class="text-center">
                <q-btn
                  color="orange"
                  icon="edit"
                  flat
                  dense
                  @click="editAddOn(props.row)"
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
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialogVisible">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ isEdit ? 'Edit Add-On' : 'Add New Add-On' }}</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="addOn.ao_name"
            label="Add-On Name"
            outlined
            dense
            class="q-mb-md"
            :rules="[val => !!val || 'Name is required']"
            required
          />
          <q-input
            v-model.number="addOn.ao_price"
            label="Price"
            type="number"
            outlined
            dense
            class="q-mb-md"
            :rules="[val => !!val || 'Price is required']"
            required
          />
          <q-select
            v-model="addOn.ao_status"
            :options="['Available', 'Not Available']"
            label="Status"
            outlined
            dense
            class="q-mb-md"
          />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="saveAddOn" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="deleteDialogVisible" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="delete" color="red" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this add-on?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="red" @click="deleteAddOn" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import axios from 'axios';
import { useQuasar } from 'quasar';

export default {
  setup() {
    const $q = useQuasar();
    return { $q };
  },
  data() {
    return {
      loading: true,
      searchQuery: '',
      AddsOns: [],
      dialogVisible: false,
      deleteDialogVisible: false,
      isEdit: false,
      addOnToDelete: null,
      addOn: {
        addOn_id: null,
        ao_name: '',
        ao_price: '',
        ao_status: 'Available'
      },
      columns: [
        { name: 'addOn_id', label: 'ID', align: 'left', field: (row) => row.addOn_id  },
        { name: 'addOn_name', label: 'Name', align: 'left', field: (row) => row.ao_name },
        { name: 'addOn_price', label: 'Price', align: 'center', field: (row) => row.ao_price },
        { name: 'action', label: 'Action', align: 'center' }
      ],
    };
  },
  computed: {
    filteredAddsOns() {
      if (!this.searchQuery.trim()) {
        return this.AddsOns.filter(addOn => addOn.ao_status !== 'Deleted');
      }
      return this.AddsOns.filter(addOn =>
        addOn.ao_status !== 'Deleted' &&
        addOn.ao_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  methods: {
    openDialog(addOn = null) {
      if (addOn) {
        this.isEdit = true;
        this.addOn = { ...addOn };
      } else {
        this.isEdit = false;
        this.resetForm();
      }
      this.dialogVisible = true;
    },

    resetForm() {
      this.addOn = {
        addOn_id: null,
        ao_name: '',
        ao_price: '',
        ao_status: 'Available'
      };
    },

    async saveAddOn() {
      try {
        const formData = {
          aoname: this.addOn.ao_name,
          aoprice: this.addOn.ao_price,
          aostatus: this.addOn.ao_status
        };

        if (this.isEdit) {
          formData.addOn_id = this.addOn.addOn_id;
          await axios.post(
            'http://localhost/raj-express/backend/controller/addOnController/update.php',
            formData
          );
        } else {
          await axios.post(
            'http://localhost/raj-express/backend/controller/addOnController/add.php',
            formData
          );
        }

        await this.fetchAddsOns();
        this.dialogVisible = false;
        this.$q.notify({
          type: 'positive',
          message: `Add-on ${this.isEdit ? 'updated' : 'added'} successfully`,
          position: 'top'
        });
      } catch (error) {
        console.error('Error saving add-on:', error);
        this.$q.notify({
          type: 'negative',
          message: error.response?.data?.error || 'Error saving add-on',
          position: 'top'
        });
      }
    },

    editAddOn(addOn) {
      this.isEdit = true;
      this.addOn = { ...addOn };
      this.dialogVisible = true;
    },

    confirmDelete(addOn) {
      this.addOnToDelete = addOn;
      this.deleteDialogVisible = true;
    },

    async deleteAddOn() {
      if (!this.addOnToDelete) return;

      try {
        const response = await axios.post(
          'http://localhost/raj-express/backend/controller/addOnController/update.php',
          {
            addOn_id: this.addOnToDelete.addOn_id,
            aoname: this.addOnToDelete.ao_name,
            aoprice: this.addOnToDelete.ao_price,
            aostatus: 'Deleted'
          }
        );

        if (response.data.success) {
          await this.fetchAddsOns();
          this.$q.notify({
            type: 'positive',
            message: 'Add-on deleted successfully',
            position: 'top'
          });
        }
      } catch (error) {
        console.error('Error deleting add-on:', error);
        this.$q.notify({
          type: 'negative',
          message: 'Error deleting add-on',
          position: 'top'
        });
      } finally {
        this.addOnToDelete = null;
        this.deleteDialogVisible = false;
      }
    },

    async fetchAddsOns() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/addOnController/get.php');
        if (response.data.success) {
          this.AddsOns = response.data.addOnsItems;
        } else {
          console.error('Failed to fetch add-ons:', response.data.error);
        }
      } catch (error) {
        console.error('AddOn error:', error);
        this.$q.notify({
          type: 'negative',
          message: error.response?.data?.error || 'Error fetching add-ons',
          position: 'top'
        });
      }
    },

    onRequest(props) {
      const { page, rowsPerPage } = props.pagination;
      this.pagination.page = page;
      this.pagination.rowsPerPage = rowsPerPage;
    }
  },
  mounted() {
    this.fetchAddsOns();
  }
};
</script>

<style scoped>
.q-page {
  padding: 20px;
}
</style>
