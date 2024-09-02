# Inertia.js Adapter for Concrete CMS
**This project and its documentation are still under construction - it is still in an experimental state and future changes can break compatibility with previous versions until a formal release is published.**  
This is an unofficial community adapter for using Inertia.js with Concrete CMS.  
The following documentation will only highlight the _changes_ made to the Laravel adapter. For the full documentation, please see [the official Inertia.js documentation](https://inertiajs.com/).  
This project is licensed under the Apache-2.0 license. A copy of the license can be found in `LICENSE.txt`

## Installation

### Server-side
To install the Inertia.js adapter into Concrete CMS, perform the following steps:  
1. Start with a **fresh** installation of Concrete CMS. Installing the adapter onto existing sites can and will break things! Instructions on how to set up and install Concrete CMS can be found [on their website](https://documentation.concretecms.org/9-x/developers/introduction/installing-concrete-cms). **Note:** For the installation type, select "empty site" or "blank site" option.
2. Copy the "inertia_ccms_adapter" folder into the site's "packages" directory.
3. Visit the "Extend Concrete" page while logged in to the CMS.
4. Install the "Inertia.js" package.

#### Root template
The root template for Inertia in this adapter is located at `inertia_ccms_adapter/themes/inertia/default.php`. There are no blade directives to add - the initial setup is already done for you. Simply make sure your bundle is pointed to the correct location (see below).

### Client-side
No major changes. **However,** when setting up your client-side scripts, there are two things to look out for:
1. Ensure your build step outputs your bundle to "packages/inertia_ccms_adapter/themes/inertia/js/app.bundle.js".
2. Do not put your frontend scripts in the "application/js" folder. This introduces some weird overriding of core CMS javascript.

## The Basics

### Pages
No major changes to the Inertia documentation.

### Responses

#### Root Template Data
The `$page` variable is available directly inside of `inertia_ccms_adapter/themes/inertia/default.php`. It maintains the structure of the Laravel implementation.  

The `withViewData()` method on `Inertia::render()` is not supported at this time. Support may be added in the future.

### Redirects
Stub.

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

#### Shorthand Routes
You cannot use `Router::inertia()` in this adapter (it doesn't exist). A global function has been added to mimic this functionality. So in your web.php or api.php route file:  

`Router::inertia('/myroute', 'MyComponent');`  

becomes  

`interia_route('/myroute', 'MyComponent', [], $router);`

You **must** pass the $router variable as the 4th parameter, so add an empty array for props if you don't have any to define.

#### Generating URLs
In Concrete, you _can_ name your routes by piping the route into the  'setName' function . For example, to add the name of "home" to your homepage route, you could write:  

```
$router->get('/', function(){ 
    return Inertia::render('HomePage'); 
})->setName('home');
```

No equivalent to Ziggy currently exists for Concrete CMS, so if you want to use your named routes client-side you'll need to pass them as props as described in the Inertia documentation.
  
To get a route URI by its name in Concrete:  
`$uri = $router->getRoutes()->get('routename')->getPath();`

### Title & meta
Stub.

### Links
Stub.

### Manual Visits
Stub.

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
Stub. Will leverage Concrete's User functionality to get the current user and its groups. Documentation will also be added to show how to wrap specific routes inside auth groups to check against a logged in user's group (or require users of a specific group to be logged in).

### Authorization
Stub. Similar to the Laravel implementation, authorization can be handled using Concrete's built-in permissions and permission objects. Once the permissions have been validated server-side, you can pass the results out to your props.

### CSRF Protection
Stub. Concrete has a built-in token library that can be used to generate and validate tokens. In practice, you'd generate a token on a response going out to the user and store it as part of the submission data that would be coming back to the server (e.g. as a hidden form field). Then, in the appropriate controller, you'd validate that token. See [this page on the CMS documentation](https://documentation.concretecms.org/developers/security/protecting-against-csrf-with-token-validation) for more information. (This will be updated with an inertia-specific imlementation in the future).

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
