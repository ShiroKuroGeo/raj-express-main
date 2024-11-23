<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-red text-white">
      <q-toolbar>
        <q-btn flat dense round icon="arrow_back" @click="goToMenu" />
        <q-toolbar-title>My Profile</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page-container >
      <q-page :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'" class="q-pt-lg">
        <div :class="[
          'profile-card q-ma-sm q-pa-md rounded-borders',
          $q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'
        ]">
          <div class="profile-picture-container q-mb-md">
            <q-avatar size="100px" class="bg-grey-3">
              <img :src="profilePicture ? `http://localhost/raj-express/backend/uploads/${profilePicture}` : `http://localhost/raj-express/backend/uploads/profile.jpg`">
              <q-btn round color="red" icon="edit" size="sm" class="edit-icon" @click="addImage" />
              <input type="file" id="file-input" @change="profileImage" name="file-input" class="hidden">
            </q-avatar>
          </div>

          <q-input v-model="first_name"
            label="First Name"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-input v-model="last_name"
            label="Last Name"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-input v-model="email"
            label="Email"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>

          <q-input v-model="contact_number"
            label="Phone Number"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="phone" />
            </template>
          </q-input>

          <q-input v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="Password"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="togglePasswordVisibility"
              />
            </template>
          </q-input>

          <q-input v-model="confirmpassword"
            :type="showPassword ? 'text' : 'password'"
            label="Confirm Password"
            outlined
            dense
            class="q-mb-sm"
            :dark="$q.dark.isActive"
            :color="$q.dark.isActive ? 'white' : 'black'">
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="togglePasswordVisibility"
              />
            </template>
          </q-input>

          <q-btn label="Update" color="red" class="full-width q-mt-md" @click="updateProfile" />

        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useQuasar } from 'quasar';
import axios from 'axios';

export default{
  data(){
    return{
      showPassword: false,
      first_name: '',
      profilePicture: '',
      last_name: '',
      email: '',
      contact_number: '',
      password: '',
      confirmpassword: '',
    }
  },
  methods:{
    async getAllUserInformation(){
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost/raj-express/backend/controller/userController/profile.php', {
          headers: {
            'Authorization': token
          }
        });

        this.first_name = response.data.fn;
        this.last_name = response.data.ln;
        this.email = response.data.mail;
        this.contact_number = response.data.number;
        this.profilePicture = response.data.profilePicture;

      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    togglePasswordVisibility(){
      showPassword.value = !showPassword.value;
    },
    goToMenu(){
      this.$router.push("/menu");
    },
    addImage(){
      document.getElementById("file-input").click();
    },
    async profileImage(e){
      try {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('profile_picture', file);

        const token = localStorage.getItem('token');
        if (!token) {
          console.error('No token found');
          return;
        }

        const response = await axios.post('http://localhost/raj-express/backend/controller/userController/uploadProfile.php', formData, {
          headers: {
            'Authorization': token,
            'Content-Type': 'multipart/form-data'
          }
        });

        if(response.status == 200){
          alert('Success');
          this.getAllUserInformation();
        }else{
          alert('Unsuccessful Update!');
        }

      } catch (error) {
        console.error('Upload error:', error.response ? error.response.data : error.message);
      }

    },
    async updateProfile (){
      try {

          const token = localStorage.getItem('token');

          const data = {
            firstName: this.first_name,
            lastName: this.last_name,
            email: this.email,
            number: this.contact_number,
            password: this.password == '' ? null : this.password,
            user_id: token
          };

          const response = await fetch("http://localhost/raj-express/backend/controller/userController/changeInformation.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(data)
          });

          if (!response.ok) {
            const errorMessage = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
          }

          const result = await response.json();
          console.log(result);

          if (result) {
            alert('Updated information!');
            // $q.notify({
            //   color: 'positive',
            //   message: result.success
            // })
          } else {
            throw new Error(result.error || "Failed to add product to cart");
          }

      } catch (error) {
        console.log('Error in ' + error);
      }
    }
  },
  created(){
    this.getAllUserInformation();
    this.profileImage();
  }
}

</script>

<style scoped>
.profile-card {
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.profile-picture-container {
  display: flex;
  justify-content: center;
}

.edit-icon {
  position: absolute;
  bottom: 0;
  right: 0;
}

.q-input {
  margin-bottom: 15px;
}

/* Dark mode specific styles */
.bg-dark {
  background: #1d1d1d;
}

.text-white {
  color: #ffffff;
}

.text-black {
  color: #000000;
}
</style>

