<template>
  <q-page padding>
    <q-table
      :rows="customers"
      :columns="columns"
      row-key="user_id"
      title="Users"
      class="text-capitalize"
    >
      <template v-slot:body-cell-action="props">
        <q-btn
          flat
          icon="visibility"
          @click="viewCustomer(props.row.user_id)"
        />
        <q-btn
          flat
          color="red"
          icon="edit"
          @click="deactiveUser(props.row.status, props.row.user_id)"
        />
      </template>
    </q-table>
  </q-page>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      customers: [],
      columns: [
        { name: "user_id", label: "User ID", align: "left", field: (row) => row.user_id},
        { name: "first_name", label: "First Name", align: "left", field: (row) => row.first_name},
        { name: "last_name", label: "Last Name", align: "left", field: (row) => row.last_name},
        { name: "address", label: "Address", align: "left", field: (row) => row.address},
        { name: "contact_number", label: "Contact Number", align: "left", field: (row) => row.contact_number},
        { name: "status", label: "Status", align: "left", field: (row) => row.status},
        { name: "action", label: "Actions", align: "left" }
      ],
    };
  },
  methods: {
    async fetchCustomers() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/adminController/userController/userList.php');
        this.customers = response.data.userDatas;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    viewCustomer(id) {
      this.$router.push({ name: 'CustomerDetails', params: { id } });
    },
    async deactiveUser(status, userId) {
      status = status == 'active' ? 'inactive' : 'active';

      if(confirm('Are you sure want to ' + status)){

        const userUpdate = {
          status: status,
          user_id: userId,
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/adminController/userController/updateStatus.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(userUpdate)
        });

        if (!response.ok) {
          const errorMessage = await response.text();
          throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
        }

        const result = await response.json();

        if (result) {
          this.fetchCustomers();
          alert('Updated!');
          // $q.notify({
          //   color: 'positive',
          //   message: result.success
          // })
        } else {
          throw new Error(result.error || "Failed to add product to cart");
        }
      }else{
        alert('No changes!');
      }

  }
  },
  mounted() {
    this.fetchCustomers();
  },
};
</script>

<style scoped>
.q-table__title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #555;
}
</style>
