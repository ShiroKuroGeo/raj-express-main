<template>
  <q-layout view="lHh Lpr lFf">
    <!-- Page Container -->
    <q-page-container>
    <!-- Page Content -->
    <q-page>
      <div class="q-pa-md">
        <!-- Profile Card -->
        <q-card class="my-card q-pa-lg q-mb-md">
          <div class="profile-container">
            <q-avatar size="100px" class="bg-grey-3">
              <img :src="profilePicture ? `http://localhost/raj-express/backend/uploads/${profilePicture}` : `http://localhost/raj-express/backend/uploads/profile.jpg`">
            </q-avatar>
            <div class="profile-info q-ml-md">
              <div class="profile-name">{{ profile.fullName || 'Loading...' }}</div>
              <div class="profile-email">{{ profile.email || 'Loading...' }}</div>
            </div>
            <!-- Dark Mode Toggle -->
            <q-btn flat round @click="toggleDarkMode">
              <q-icon :name="darkMode ? 'brightness_4' : 'brightness_7'" />
            </q-btn>
          </div>
        </q-card>
        <br><br>
        <p>More</p>

        <!-- Profile Options List -->
        <q-card class="q-pa-none q-mb-md">
          <q-list bordered>
            <q-item clickable v-ripple @click="goToProfile">
              <q-item-section avatar>
                <q-icon name="person" />
              </q-item-section>
              <q-item-section>Profile</q-item-section>
            </q-item>

            <q-item clickable v-ripple @click="goToOrder">
              <q-item-section avatar>
                <q-icon name="shopping_bag" />
              </q-item-section>
              <q-item-section>My Order</q-item-section>
            </q-item>

            <!-- Sa order rani siya ayaw ibutang ang mga details2 -->

            <!-- <q-item clickable v-ripple @click="goToOrderDetails">
              <q-item-section avatar>
                <q-icon name="receipt_long" />
              </q-item-section>
              <q-item-section>Order Details</q-item-section>
            </q-item> -->

            <q-item clickable v-ripple @click="goToNotifications">
              <q-item-section avatar>
                <q-icon name="notifications" />
              </q-item-section>
              <q-item-section>Notification</q-item-section>
            </q-item>

            <q-item clickable v-ripple @click="goToAddress">
              <q-item-section avatar>
                <q-icon name="place" />
              </q-item-section>
              <q-item-section>Address</q-item-section>
            </q-item>

            <q-item clickable v-ripple @click="goToMessages">
              <q-item-section avatar>
                <q-icon name="message" />
              </q-item-section>
              <q-item-section>Message</q-item-section>
            </q-item>

            <q-item clickable v-ripple @click="deleteAccount">
              <q-item-section avatar>
                <q-icon name="delete" />
              </q-item-section>
              <q-item-section>Delete Account</q-item-section>
            </q-item>

            <q-item clickable v-ripple @click="logout">
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>Log out</q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>
    </q-page>
  </q-page-container>

  </q-layout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import axios from 'axios';

export default {
  setup() {
    const $q = useQuasar();
    const router = useRouter();
    const darkMode = ref($q.dark.isActive);
    const profile = ref({
      fullName: '',
      email: ''
    });
    const profilePicture = ref(null);

    const fetchUserData = async () => {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No auth token found');
        }

        const response = await axios.get('http://localhost/raj-express/backend/controller/profile.php', {
          headers: {
            'Authorization': `${token}`
          }
        });

        const data = response.data;
        console.log(data);
        if (data) {
          profile.value = {
            fullName: `${data.first_name} ${data.last_name}`,
            email: data.email
          };
          profilePicture.value = data.profile_img;
        } else {
          throw new Error(data.error || 'Failed to fetch user data');
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
        $q.notify({
          color: 'negative',
          message: 'Failed to load profile data. Please try again.',
          icon: 'report_problem'
        });
      }
    };

    onMounted(() => {
      fetchUserData();
    });

    const toggleDarkMode = () => {
      $q.dark.toggle();
      darkMode.value = $q.dark.isActive;
    };

    const goToProfile = () => router.push('/profile');
    const goToOrder = () => router.push('/order');
    const goToOrderDetails = () => router.push('/order-details');
    const goToNotifications = () => router.push('/notifications');
    const goToAddress = () => router.push('/address');
    const goToMessages = () => router.push('/messages');

    const deleteAccount = async () => {
      try {
        // Show confirmation dialog
        $q.dialog({
          title: 'Confirm Deletion',
          message: 'Are you sure you want to delete this account? This action cannot be undone.',
          cancel: true,
          persistent: true,
          ok: {
            color: 'negative',
            label: 'Delete'
          },
          cancel: {
            color: 'grey',
            label: 'Cancel'
          }
        }).onOk(async () => {
          const token = localStorage.getItem('token');
          const response = await fetch('http://localhost/raj-express/backend/controller/userController/deletionAccount.php', {
            headers: {
              'Authorization': token
            }
          });

          if (!response.ok) {
            const errorMessage = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
          }

          const result = await response.json();
          $q.notify({
            type: 'positive',
            message: 'Account deleted successfully',
            position: 'top',
            timeout: 2000
          });
          router.push('/');
        }).onCancel(() => {
          $q.notify({
            type: 'positive',
            message: 'Account deletion cancelled',
            position: 'top',
            timeout: 2000
          });
        });

      } catch (error) {
        $q.notify({
          type: 'negative',
          message: 'Error deleting account: ' + error.message,
          position: 'top'
        });
      }
    };

    const logout = () => {
      $q.dialog({
        title: 'Confirm Logout',
        message: 'Are you sure you want to log out?',
        cancel: true,
        persistent: true,
        ok: {
          color: 'red',
          label: 'Logout'
        },
        cancel: {
          color: 'grey',
          label: 'Cancel'
        }
      }).onOk(() => {
        localStorage.removeItem('token');
        router.push('/');
        $q.notify({
          type: 'positive',
          message: 'Logged out successfully',
          position: 'top',
          timeout: 2000
        });
      }).onCancel(() => {
        $q.notify({
          type: 'positive',
          message: 'Logout cancelled',
          position: 'top',
          timeout: 2000
        });
      });
    };

    return {
      darkMode,
      profile,
      profilePicture,
      toggleDarkMode,
      goToProfile,
      goToOrder,
      goToOrderDetails,
      goToNotifications,
      goToAddress,
      goToMessages,
      deleteAccount,
      logout
    };
  }
};
</script>

<style scoped>
.my-card {
  max-width: 400px;
  margin: auto;
}

.profile-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.profile-name {
  font-weight: bold;
  font-size: 16px;
}

.profile-email {
  font-size: 14px;
  color: grey;
}



.bg-orange {
  background-color: #ffa500 !important;
}

.text-orange {
  color: #ffa500 !important;
}

.q-footer {
  box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
}



.footer-fixed {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60px;
  box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
  z-index: 999; /* Ensure the footer is above other content */

}
.q-tab-active {
  border-radius: 5px;
  padding: 10px;
  transform: translateY(-10px);
}

</style>
