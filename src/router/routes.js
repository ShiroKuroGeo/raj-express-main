const routes = [

  {
    path: '/',
    component: () => import('pages/Login.vue')
  },
  {
    path: '/register',
    component: () => import('pages/Register.vue')
  },
  {
    path: '/reset-password',
    component: () => import('pages/reset.vue')
  },
  {
    path: '/change-password/:token',
    component: () => import('pages/resetpassword.vue')
  },
  {
    path: '/admin',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: '/admin-dashboard', component: () => import('pages/admin/Dashboard.vue') },
      { path: '/dashboard', component: () => import('pages/admin/Dashboard.vue') },
      { path: '/settings', component: () => import('pages/admin/Settings.vue') },
      { path: '/notification', component: () => import('pages/admin/Notification.vue') },
    ],
  },
  {
    path: '/pos',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'new-sale', name: 'new-sale', component: () => import('pages/admin/PosNewSale.vue') },
      { path: 'product-add', name: 'product-add', component: () => import('pages/admin/PosProductAdd.vue') },
      { path: 'categories', component: () => import('pages/admin/PosCategory.vue') },
      { path: 'orders', name: 'orders', component: () => import('pages/admin/PosOrders.vue') },
      { path: 'details/:id', name: 'pos-order-details', component: () => import('pages/admin/PosViewDetails.vue') },
      { path: 'products', name: 'products', component: () => import('pages/admin/PosProducts.vue') },
      { path: 'print-order/:id', name: 'pos-print-order', component: () => import('pages/admin/PosPrintOrder.vue') },
      { path: 'pos-sale', component: () => import('pages/admin/PosOrderSales.vue') },
    ]
  },
  {
    path: '/orders',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'all', component: () => import('src/pages/admin/orders/All_Orders.vue') },
      { path: 'pending', component: () => import('pages/admin/orders/Pending_Orders.vue') },
      { path: 'confirm', component: () => import('pages/admin/orders/Confirm_Orders.vue') },
      { path: 'processing', component: () => import('src/pages/admin/orders/Processing_Orders.vue') },
      { path: 'delivered', component: () => import('src/pages/admin/orders/Delivered_Orders.vue') },
      { path: 'returned', component: () => import('src/pages/admin/orders/Returned_Orders.vue') },
      { path: 'canceled', component: () => import('src/pages/admin/orders/Canceled_Orders.vue') },
    ],
  },
  {
    path: '/products',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'add', component: () => import('pages/admin/Product_Add_U.vue') },
      { path: 'list', component: () => import('pages/admin/Product_List_U.vue') },
      { path: 'categories', component: () => import('pages/admin/Product_Category_U.vue') },
      { path: 'extras', component: () => import('pages/admin/Product_Extra_U.vue') },
      { path: 'reviews', component: () => import('pages/admin/Product_Reviews_U.vue') },
    ],
  },
  {
    path: '/support',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'message', component: () => import('pages/admin/Message.vue') },
      { path: 'message/:id', name: 'messageSomeone', component: () => import('pages/admin/Messages.vue') },
    ],
  },
  {
    path: '/reports',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'earnings', component: () => import('pages/admin/Earning_Reports.vue') },
      { path: 'orders', component: () => import('pages/admin/Orders_Reports.vue') },
      { path: 'sale', component: () => import('pages/admin/Sale_Report.vue') },
      { path: 'expenses', component: () => import('pages/admin/Expenses_Reports.vue') },
      { path: 'summary', component: () => import('pages/admin/ExpensesSummary.vue') },
    ]
  },
  {
    path: '/list',
    component: () => import('layouts/AdminLayout.vue'),
    children: [
      { path: 'customers', component: () => import('pages/admin/Customer_List.vue') },
      { path: 'invoices', component: () => import('pages/admin/Customer_Invioce.vue') },
      { path: 'customer-orders/:id', name: 'customerOrderDetails', component: () => import('src/pages/admin/Customer_viewOrders.vue') },
      { path: 'customer-details/:id', name: 'CustomerDetails', component: () => import('pages/admin/Customer_Details.vue') },
    ],
  },
  {
    path: '/users',
    component: () => import('layouts/UserLayout.vue'),
    children: [
      { path: '/home', name: 'home', component: () => import('pages/user/Home_Page.vue') },
      { path: '/product-details/:id', name: 'productDetails', component: () => import('pages/user/Product_View.vue') },
      { path: '/favorites', name: 'favorites', component: () => import('pages/user/Hearts_Page.vue') },
      { path: '/cart', component: () => import('src/pages/user/Cart_Page.vue') },
      { path: '/add-cart/:id', name: 'addProduct', component: () => import('pages/user/Cart_Add.vue') },
      { path: '/update-cart/:id', name: 'updateProduct', component: () => import('pages/user/Cart_Update.vue') },
      { path: '/cart-not-empty', component: () => import('pages/user/Cart_NotEmpty.vue') },
      { path: '/place-order', name: 'placeOrder', component: () => import('pages/user/Place_order.vue') },
      { path: '/order', name: 'order', component: () => import('pages/user/Order_Page.vue') },
      { path: '/order-details/:id', name: 'order-details', component: () => import('pages/user/Order_details.vue') },
      { path: '/profile', name: 'profile', component: () => import('pages/user/UpdateProfile_Page.vue') },
      { path: '/menu', name: 'menu', component: () => import('pages/user/Menu_Page.vue') },
      { path: '/my-order', component: () => import('pages/user/MyOrderPage.vue') },
      { path: '/address', component: () => import('pages/user/Address.vue') },
      { path: '/messages', component: () => import('pages/user/User_Message.vue') },
      { path: '/delete-account', component: () => import('pages/user/DeleteAccountPage.vue') },
      { path: '/logout', component: () => import('pages/user/LogoutPage.vue') },
      { path: '/checkout', component: () => import('pages/user/CheckOut_Page.vue') },
      { path: '/rating', component: () => import('pages/user/Rating_Page.vue') },
      { path: '/notifications', component: () => import('pages/user/Notification.vue') },
      { path: '/payment-success/:id', component: () => import('pages/user/successOnlinePayment.vue') },
      { path: '/product-order-rating/:id', name: 'productRating', component: () => import('pages/user/rateOrder.vue') },
    ]
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
