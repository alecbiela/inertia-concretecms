# Inertia.js Adapter for Concrete CMS
**This project and its documentation are still under construction - it is still in an experimental state and future changes can break compatibility with previous versions until a formal release is published.**  
This is an unofficial community adapter for using Inertia.js with Concrete CMS.  
The following documentation will only highlight the _changes_ made to the Laravel adapter. For the full documentation, please see [the official Inertia.js documentation](https://inertiajs.com/).  

## Installation

### Server-side
To install the Inertia.js adapter into Concrete CMS, perform the following steps:  
1. Start with a **fresh** installation of Concrete CMS. Installing the adapter onto existing sites can and will break things! Instructions on how to set up and install Concrete CMS can be found [on their website](https://documentation.concretecms.org/9-x/developers/introduction/installing-concrete-cms). **Note:** For the installation type, select the "Empty Site" option.
2. After installation is finished, copy the "inertia_ccms_adapter" folder into the site's "packages" directory.
3. Log in to the CMS.
4. Visit the "Extend Concrete" page in the dashboard and install the "Inertia.js" package.

#### Root template
The root template for Inertia in this adapter is located at `inertia_ccms_adapter/themes/inertia/default.php`. There are no blade directives to add - the initial setup is already done for you. Simply make sure your bundle is pointed to the correct location (see below).

### Client-side
No major changes. **However,** when setting up your client-side scripts, there are two things to look out for:
1. Ensure your build step outputs your bundle to the following locations:  
**Javascript:** `packages/inertia_ccms_adapter/themes/inertia/js/app.bundle.js`    
**CSS:** `packages/inertia_ccms_adapter/themes/inertia/css/app.bundle.css`  
2. Do not put your frontend scripts in the "application/js" folder. This introduces some weird overriding of core CMS javascript.

## The Basics

### Responses

#### Root Template Data
The `$page` variable is available directly inside of `inertia_ccms_adapter/themes/inertia/default.php`. It maintains the structure of the Laravel implementation.  

You may also add additional page variables using the `withViewData()` method chained onto an Inertia render response. These will be available in the page template the same as `$page`, but will not be passed to the frontend framework.

### Redirects
Handling redirects is very similar to the Laravel implementation. You will need to include `use Concrete\Core\Routing\Redirect;` at the top of the file you want to redirect in. Then, in your method, write something like:  
`return Redirect::url('/my_url')`

#### 303 Response Code
This is done for you. Since the `Redirect::url()` method in Concrete returns a 302 response, this is automatically converted to a 303 response by Inertia.

### Routing

#### Defining Routes
Define your routes in the `inertia_ccms_adapter/routes` directory. The **web.php** and **api.php** function similar to Laravel, where the API routes will be auto-prefixed with '/api/'. For route controllers, the API controllers should live in `inertia_ccms_adapter/controllers/api/` and have a namespace of `Concrete\Package\InertiaCcmsAdapter\Controller\Api`. You can then reference them in the router as `ApiControllerName::method` in the 2nd parameter.  

Certain top-level routes are already registered by Concrete and it is recommended that you avoid defining custom routes to or underneath them. These are:  
* `/account`
* `/dashboard`
* `/desktop`
* `/download_file`
* `/login`
* `/register`
* `/page_forbidden`
* `/page_not_found`

#### Shorthand Routes
You cannot use `Router::inertia()` in this adapter (it doesn't exist). A global function has been added to mimic this functionality. So in your web.php or api.php route file:  

`Router::inertia('/myroute', 'MyComponent');`  

becomes  

`interia_route('/myroute', 'MyComponent', [], $router);`

You **must** pass the $router variable as the 4th parameter, so add an empty array for props if you don't have any to define.

#### Generating URLs
In Concrete, you _can_ name your routes by piping the route into the 'setName' function. For example, to add the name of "home" to your homepage route, you could write:  

```
$router->get('/', function(){ 
    return Inertia::render('HomePage'); 
})->setName('home');
```
  
To retrieve a route by its name, this adapter includes a static `getUriByName` method in `InertiaRouter\InertiaRouter`:  
`$uri = InertiaRouter::getUriByName('home'); // Will return NULL if the route isn't found`

No equivalent to Ziggy currently exists for Concrete CMS, so if you want to use your named routes client-side you'll need to pass them as props as described in the Inertia documentation.

### Title & meta
The `<Head>` component works in accordance with the original Inertia documentation. Most meta tags and the `<title>` tag have been stripped from the Concrete head, so you will need to define them at the page-level in your components/layouts. There are a few notable exceptions to this:  
* The favicons and thumbnails for your site should be set up inside the CMS at Dashboard > System &amp; Settings > Basics > Bookmark Icons
* SEO tracking code (e.g. Google Analytics/Google Tag Manager) should be set up inside the CMS at Dashboard > System &amp; Settings > SEO &amp; Statistics > Tracking Codes
* The `<meta charset='...'>` tag is included automatically

#### Title Callback
A global variable `CCM_SITE_NAME` will be exposed with the name of the site as specified inside Dashboard > System &amp; Settings > Basics > Name &amp; Attributes (defaults to the "Site Name" you provide during Concrete CMS installation). You can retrieve this when setting your page titles, or even pipe it into your title callback, like so:  
```
createInertiaApp({
  title: title => `${title} :: ${CCM_SITE_NAME}`,
  // ...
})
```

### Forms
Stub.

### File Uploads
Stub.

### Validation
Stub.

### Shared data
Stub.

## Advanced

### Events
Stub.

### Testing
Stub.

### Partial Reloads
Stub.

### Scroll Management
Stub.

### Authentication
#### Defining Auth Routes
Chain the `addMiddleware()` method onto the route you require authentication for. Consider the example below:
```
$router->get('/users/{id}', function($id) {
    return Inertia::render('Users/View', [
        'name' => User::getByUserID($id)->getUserName()
    ]);
})->addMiddleware(InertiaAuthMiddleware::class);
```
When users attempt to access routes with the `Auth` middleware, they will be redirected to the CMS login page if they are not already logged in. If they successfully log in, they will be redirected back to the original page they were attempting to access.  

#### Defining a Custom Auth Group
There may be situations where you need to target a specific user group for authentication. In that case, you can modify the config value in `inertia_ccms_adapter/config/inertia.php` for `user_settings.auth_user_group` to the name of a specific group inside the CMS (for example, "Administrators"). This will check the user's groups when logging in and, if they do successfully log in but aren't a part of that group, will be served a "Page Forbidden" error page. The content and design of this page can be adjusted inside `inertia_ccms_adapter/themes/inertia/page_forbidden.php`.

#### Site-Wide Authentication
Follow these instructions for protecting your entire site behind authentication:
1. Log in to the CMS and visit the Sitemap page (`/dashboard/sitemap/full`). 
2. Click on the "Home" page entry, then click on "Permissions" in the menu that appears.
3. Under "Who can view this page?", deselect "Guest" and then select the user group(s) that you would like to use. 
4. Click "Save Changes" at the bottom of the pop-up window.

Your entire site is now restricted to users in the groups that you selected. To restore logged-out access, simply repeat the steps but de-select any custom groups and re-select the "Guest" entry in the Permissions menu.

### Authorization
Stub. Similar to the Laravel implementation, authorization can be handled using Concrete's built-in permissions and permission objects. Once the permissions have been validated server-side, you can pass the results out to your props.

### CSRF Protection
Stub. Concrete has a built-in token library that can be used to generate and validate tokens. In practice, you'd generate a token on a response going out to the user and store it as part of the submission data that would be coming back to the server (e.g. as a hidden form field). Then, in the appropriate controller, you'd validate that token. See [this page on the CMS documentation](https://documentation.concretecms.org/developers/security/protecting-against-csrf-with-token-validation) for more information. (This will be updated with an inertia-specific implementation in the future).

### Error handling
Stub.

### Asset versioning
Stub.

### Progress indicators
Stub.

### Remembering state
Stub.

### Server-side rendering
Stub.

## License Information
* This package: Apache-2.0 License, found at `LICENSE.txt`
* Inertia.js: MIT License, found at `inertia_ccms_adapter/src/Inertia/LICENSE.md`
* Concrete CMS: MIT License, found at [this link](https://github.com/concretecms/concretecms/blob/9.3.x/LICENSE.TXT), also in the root directory of any Concrete5 or Concrete CMS installation.
