<template>
  <q-page padding class="settings-page">
    <h2 class="settings-title">Settings</h2>

    <!-- Admin dapat walay profile picture tungod kay isa ray tindero (Usa ray mo tinda) -->
    <!-- <div class="profile-image-container">
      <div class="profile-image-wrapper">
        <q-img
          v-if="profile_img"
          :src="'http://localhost/raj-express/backend/uploads/' + profile_img"
          class="profile-image"
        />
        <q-btn
          round
          color="primary"
          icon="edit"
          size="sm"
          class="edit-image-button"
          @click="$refs.fileInput.click()"
        />
      </div>
      <input
        type="file"
        ref="fileInput"
        @change="handleImageUpload"
        accept="image/*"
        style="display: none;"
      />
    </div> -->

    <q-form class="settings-form">
      <q-card class="settings-card">
        <q-card-section>
          <div class="section-title">Basic information</div>
          <div class="input-row">
            <label>Firstname</label>
            <q-input v-model="first_name" filled />
          </div>
          <div class="input-row">
            <label>Lastname</label>
            <q-input v-model="last_name" filled />
          </div>
          <div class="input-row">
            <label>Email</label>
            <q-input v-model="email" type="email" filled />
          </div>
          <div class="input-row">
            <label>Phone</label>
            <q-input v-model="contact_number" type="tel" filled />
          </div>
          <div class="input-row">
            <label>Address</label>
            <q-input v-model="address" filled />
          </div>
          <q-btn @click="changeInformation" label="Save Basic Information" color="orange" class="save-button" />
        </q-card-section>
      </q-card>

      <!-- Change Password Section -->
      <q-card class="settings-card">
        <q-card-section>
          <div class="section-title">Change password</div>
          <div class="input-row">
            <label>Current Password</label>
            <q-input v-model="currentPassword" :type="showCurrentPassword ? 'text' : 'password'" filled>
              <template v-slot:append>
                <q-icon :name="showCurrentPassword ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="CurrentPasswordShowHide" />
              </template>
            </q-input>
          </div>
          <div class="input-row">
            <label>New Password</label>
            <q-input v-model="newPassword" :type="showNewPassword ? 'text' : 'password'" filled>
              <template v-slot:append>
                <q-icon :name="showNewPassword ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="NewPasswordShowHide" />
              </template>
            </q-input>
          </div>
          <div class="input-row">
            <label>Confirm Password</label>
            <q-input v-model="confirmPassword" :type="confirmNewPassword ? 'text' : 'password'" filled>
              <template v-slot:append>
                <q-icon :name="confirmNewPassword ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="ConfirmPasswordShowHide" />
              </template>
            </q-input>
          </div>
          <q-btn @click="changePassword" label="Save Basic Information" color="orange" class="save-button" />
        </q-card-section>
      </q-card>
    </q-form>
  </q-page>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      user_id: '',
      first_name: '',
      last_name: '',
      address: '',
      contact_number: '',
      email: '',
      password_hash: '',
      user_type: '',
      profile_img: '',
      status: '',
      date_deletion: '',
      created_at: '',
      updated_at: '',
      newPassword: '',
      confirmPassword: '',
      currentPassword: '',
      showCurrentPassword: false,
      showNewPassword: false,
      confirmNewPassword: false,
      profileImage: null
    };
  },
  methods: {
    CurrentPasswordShowHide(){
      this.showCurrentPassword = !this.showCurrentPassword;
    },
    NewPasswordShowHide(){
      this.showNewPassword = !this.showNewPassword;
    },
    ConfirmPasswordShowHide(){
      this.confirmNewPassword = !this.confirmNewPassword;
    },
    async fetchProfile() {
      try {
        const response = await axios.get('http://localhost/raj-express/backend/controller/admincontroller/settingcontroller.php');
        this.user_id = response.data.user_id;
        this.first_name = response.data.first_name;
        this.last_name = response.data.last_name;
        this.address = response.data.address;
        this.contact_number = response.data.contact_number;
        this.email = response.data.email;
        this.password_hash = response.data.password_hash;
        this.user_type = response.data.user_type;
        this.profile_img = response.data.profile_img;
        this.status = response.data.status;
        this.date_deletion = response.data.date_deletion;
        this.created_at = response.data.created_at;
        this.updated_at = response.data.updated_at;
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async changeInformation() {
      try {
        const dataInformation = {
          first_name: this.first_name,
          last_name: this.last_name,
          email: this.email,
          contact_number: this.contact_number,
          address: this.address
        };

        const response = await fetch("http://localhost/raj-express/backend/controller/admincontroller/changeInformation.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(dataInformation)
        });

        if(response.status){
          alert('Information of admin is done!');
        }else{
          alert('Failed to change information!');
        }
       
      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    async changePassword(){
      try {
        if(this.newPassword === this.confirmPassword){
          const dataPassword = {
            userId: this.user_id,
            currentPassword: this.currentPassword,
            newPassword: this.newPassword,
          };

          axios.post('http://localhost/raj-express/backend/controller/admincontroller/changePassword.php', dataPassword)
            .then(response => {
              console.log('Password updated successfully:', response.data);
              window.location.reload();
            })
            .catch(error => {
              console.error('Failed to update password:', error.response.data);
              window.location.reload();
            });

        }else{
          alert('Password not match!');
        }

      } catch (error) {
        console.log('Error in ' + error);
      }
    },
    handleImageUpload(event) {
      
    }
  },
  created() {
    this.fetchProfile();
  }
};
</script>

<style scoped>
.settings-page {
  max-width: 800px;
  margin: 0 auto;
}

.settings-title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.profile-image-container {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.profile-image-wrapper {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #f0f0f0;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.edit-image-button {
  position: absolute;
  bottom: 5px;
  right: 5px;
  background-color: white;
  color: #1976D2;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.settings-card {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 500;
  margin-bottom: 1rem;
}

.input-row {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.input-row label {
  flex: 0 0 150px;
  font-weight: 500;
}

.input-row .q-input {
  flex: 1;
}

.save-button {
  align-self: flex-end;
  padding: 0.5rem 2rem;
  font-weight: bold;
}
</style>
