<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'" v-if="isHomePage">
      <q-toolbar>
        <q-input
          v-model="search"
          placeholder="Are you hungry?"
          outlined
          dense
          class="full-width q-mx-md search-input"
          :dark="$q.dark.isActive"
          :bg-color="$q.dark.isActive ? 'dark' : 'white'"
          :color="$q.dark.isActive ? 'white' : 'black'"
        >
          <template v-slot:prepend>
            <q-icon
              name="search"
              :color="$q.dark.isActive ? 'white' : 'black'"
            />
          </template>
        </q-input>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer elevated :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'">
      <q-tabs
        v-model="tab"
        :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'"
        active-color="red"
        indicator-color="transparent"
        align="justify"
      >
        <q-tab
          name="home"
          icon="home"
          :label="activeTab === 'home' ? 'Home' : ''"
          @click="goTo('home')"
        />
        <q-tab
          name="favorites"
          icon="favorite"
          :label="activeTab === 'favorites' ? 'Favorites' : ''"
          @click="goTo('favorites')"
        />
        <q-tab
          name="cart"
          icon="room_service"
          :label="activeTab === 'cart' ? 'Cart' : ''"
          @click="goTo('cart')"
        />
        <q-tab
          name="order"
          icon="shopping_cart"
          :label="activeTab === 'order' ? 'Order' : ''"
          @click="goTo('order')"
        />
        <q-tab
          name="menu"
          icon="menu"
          :label="activeTab === 'menu' ? 'Menu' : ''"
          @click="goTo('menu')"
        />
      </q-tabs>
    </q-footer>
  </q-layout>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'

export default {
  setup() {
    const $q = useQuasar()
    const search = ref('')
    const tab = ref('home')
    const activeTab = ref('')
    const router = useRouter()
    const route = useRoute()

    const isHomePage = computed(() => {
      return route.name === 'home' || route.path === '/home'
    })

    const goTo = (routeName) => {
      router.push(`/${routeName}`)
      tab.value = routeName
      activeTab.value = routeName

      // Reset activeTab after a short delay
      // setTimeout(() => {
      //   activeTab.value = ''
      // }, 5000) // Adjust this value to control how long the label stays visible
    }

    return {
      search,
      tab,
      activeTab,
      goTo,
      isHomePage,
      $q
    }
  }
}
</script>

<style>
.q-toolbar {
  min-height: 70px;
}

.q-footer {
  height: 70px;
}

.q-tab {
  min-height: 70px;
}

.q-tab__icon {
  font-size: 24px;
}

.q-tabs {
  height: 100%;
}

.search-input {
  transition: all 0.3s ease;
}

.search-input.q-dark {
  background: #1d1d1d;
  color: white;
}

.search-input.q-dark .q-field__native,
.search-input.q-dark .q-field__prefix,
.search-input.q-dark .q-field__suffix,
.search-input.q-dark .q-field__input {
  color: white !important;
}

.bg-dark {
  background: #1d1d1d !important;
}

.search-input.q-dark input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.q-header,
.q-footer,
.q-input {
  transition: all 0.3s ease;
}
</style>
