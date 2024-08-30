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

### Client-side
No major changes. **However,** when setting up your client-side scripts, there are two things to look out for:
1. Ensure your build step outputs your bundle to "packages/inertia_ccms_adapter/themes/inertia/js/app.bundle.js".
2. Do not put your frontend scripts in the "application/js" folder. This introduces some weird overriding of core CMS javascript.

## The Basics

### Pages
Stub.

### Responses
The `Inertia::render()->withViewData()` method is not supported at this time. Support may be added in the future.

### Redirects
Stub.

### Routing
The `Route::Inertia()` method is not supported at this time. Support may be added in the future.  
Certain top-level routes are already registered by Concrete and it is recommended that you avoid defining custom routes to or underneath them. These are:  
*`/account`
*`/dashboard`
*`/desktop`
*`/download_file`
*`/login`
*`/register`

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
Stub.

### Authorization
Stub.

### CSRF Protection
Stub.

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
