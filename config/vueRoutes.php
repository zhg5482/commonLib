<?php

$layout = '{"name":"Layout","components":{"Navbar":{"components":{"Breadcrumb":{"watch":{},"methods":{},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-b50ef614","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/components/Breadcrumb/index.vue"},"Hamburger":{"name":"Hamburger","props":{"isActive":{"default":false}},"methods":{},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-4e6f274c","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/components/Hamburger/index.vue"}},"computed":{},"methods":{},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-d16d6306","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/Navbar.vue"},"Sidebar":{"components":{"SidebarItem":{"name":"SidebarItem","components":{"Item":{"name":"MenuItem","functional":true,"props":{"icon":{"default":""},"title":{"default":""}},"__file":"src/layout/components/Sidebar/Item.vue"},"AppLink":{"props":{"to":{"required":true}},"methods":{},"staticRenderFns":[],"_compiled":true,"beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/Sidebar/Link.vue"}},"mixins":[{"computed":{},"methods":{}}],"props":{"item":{"required":true},"isNest":{"default":false},"basePath":{"default":""}},"methods":{},"staticRenderFns":[],"_compiled":true,"beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/Sidebar/SidebarItem.vue"},"Logo":{"name":"SidebarLogo","props":{"collapse":{"required":true}},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-6494804b","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/Sidebar/Logo.vue"}},"computed":{},"staticRenderFns":[],"_compiled":true,"beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/Sidebar/index.vue"},"AppMain":{"name":"AppMain","computed":{},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-078753dd","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/components/AppMain.vue"}},"computed":{},"methods":{},"staticRenderFns":[],"_compiled":true,"_scopeId":"data-v-13877386","beforeCreate":[null],"beforeDestroy":[null],"__file":"src/layout/index.vue"}';
$layout = json_decode($layout,true);
return [
    array(
        'path' => '/redirect',
        //'component' => 'layout/Layout',
        'component' => $layout,
        'hidden' => true,
        'children' => array(
            array( 'path'=>'/redirect/:path*','component'=>'views/redirect/index')
        )
    ),

    array(
        'path' => '/login',
        'component' => 'views/login/index',
        'hidden' => true,
    ),

    array(
        'path' => '/auth-redirect',
        'component' => 'views/login/auth-redirect',
        'hidden' => true,
    ),

    array(
        'path' => '/404',
        'component' => 'views/error-page/404',
        'hidden' => true,
    ),

    array(
        'path' => '/401',
        'component' => 'views/error-page/401',
        'hidden' => true,
    ),

    array(
        'path' => '',
        'component' => $layout,
        'redirect' => 'dashboard',
        'children' => array(
            array(
                'component' => 'views/dashboard/index',
                'name' => 'Dashboard',
                'path' => 'dashboard',
                'meta' => array(
                    'affix' => true,
                    'icon' => 'dashboard',
                    'title' => 'Dashboard'
                )
            )
        )
    ),


    array(
        'path' => '/profile',
        'component' => $layout,
        'redirect' => '/profile/index',
        'hidden' => true,
        'children' => array(
            array(
                'component' => 'views/profile/index',
                'name' => 'Profile',
                'path' => 'index',
                'meta' => array(
                    'noCache' => true,
                    'icon' => 'dashboard',
                    'title' => 'Profile'
                )
            )
        )
    ),

    array(
        'path' => '/icon',
        'component' => $layout,
        'children' => array(
            array(
                'component' => 'views/icons/index',
                'name' => 'Icons',
                'path' => 'index',
                'meta' => array(
                    'noCache' => true,
                    'icon' => 'icon',
                    'title' => 'Icons'
                )
            )
        )
    ),


    array(
        'path' => '/user',
        'component' => $layout,
        'redirect' => '/user/list',
        'alwaysShow'=>'true' , // will always show the root menu
        'name'=> 'User',
        'meta'=>array(
            'title'=> '用户管理',
            'icon'=> 'user',
            'roles'=> ['admin', 'editor'] // you can set roles in root nav
        ),
        'children' => array(
            array(
                'component' => 'views/user/list',
                'name' => 'list',
                'path' => 'list',
                'meta' => array(
                    'roles'=> ['admin'],
                    'title' => '用户列表'
                )
            ),
            array(
                'component' => 'views/permission/role',
                'name' => 'role',
                'path' => 'role',
                'meta' => array(
                    'roles'=> ['admin'],
                    'title' => '权限管理'
                )
            )
        )
    ),

];


