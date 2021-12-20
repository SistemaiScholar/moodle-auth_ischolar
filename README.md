![iScholarlogo](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/logo1.png)
# iScholar <> Moodle Authentication <br>Quick Guide

The **iScholar <> Moodle Authentication** integration aims to allow students and teachers to directly access Moodle from their own iScholar interfaces. This guide will demonstrate, step-by-step, the procedures for install, activate and deactivate the integration in your iScholar system and Moodle.

Before your start make sure you meet the following requirements:
* Have an installation of Moodle on a server accessible from the Internet;
* Have administrative access to the Moodle site referred to in the previous item;
* Have access permission to manage tokens in your iScholar system.

## Activating the integration

First you will need to install the **iScholar <> Moodle Authentication** plugin on your Moodle site, proceeding as follows:

* Download the **iScholar <> Moodle Authentication** plugin by clicking [here](https://github.com/SistemaiScholar/moodle-ischolar_authentication/raw/main/dist/auth_ischolar-latest.zip). Save the zip file to an easily accessible folder on your computer. Alternatively, you can search for and download the plugin through the Moodle plugins directory, available at https://moodle.org/plugins/index.php. 
* Access your Moodle website by using an administrator account. 

* From the left column menu, click `Site Administration`, click on the `Plugins` option and than click `Install Plugins`.
![Image-1](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image1.png)

* Drag the file you downloaded in the previous step to the area indicated in the figure below and click the `Install plugin from ZIP file` button. 
![Image-2](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image2.png)

* Click on the `Continue` button.
![Image-3](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image3.png)

* If the `Current release information` screen appears, just click the `Continue` button at the bottom of the page.
* If the `Plugins check` screen appears, just click the `Update Moodle Database Now` button.
* If the `Upgrading to new version` screen appears (in case you are upgrading to a new version), click on `Continue` button. 

After the procedure described above, you will have installed the plugin. Now it will be necessary to configure it in order to establish a connection between the iScholar and Moodle systems. To do this, follow the steps below (note that iScholar systems are currently only available in Portuguese, so the following screenshots will be in that language): 

* Access your iScholar system and from the `Administração` menu click on the `Configurações` option.
![Image-4](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image4.png)

* Click the `Gerenciar tokens` button.
![Image-5](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image5.png)

* On the Token Management page, click the `+ cadastrar` button if there is no valid token for the `Autenticação iScholar <> Moodle` in the `serviço` column.
* To create a new token, select the `Autenticação iScholar <> Moodle` on the `Integração` field and optionally set an expiration date for this token (you can leave it blank in order to create a permanent token). Click on the `Salvar` button.
![Image-6](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image6.png)

* Once the token is generated, you will be redirected to the previous page. Click on the icon shown in the figure below to copy the created token.
![Image-7](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image7.png)

* Back in your Moodle site, in the `Site Administration` screen, `Plugins`, click on the `iScholar <> Moodle Authentication` plugin link to access the settings page.
![Image-8](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image8.png)

* In the plugin settings screen that opens (figure below), check the `Enabled` option, paste the copied token into the text box of the `Token from iScholar` field and click `Save changes`. 
![Image-9](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image9.png)

Following the above procedure the plugin will automatically configure both Moodle and iScholar systems. To check the configuration status and ensure its correctness, simply access this plugin configuration page again. At the bottom of the page you should see something like the following figure: 
![Image-10](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image10.png)

If any of the items in the `Configuration Status` fails, check if the token in use is valid and click on the `Fix configurations` button that will appear right below the last item. The plugin should automatically fix the flaws.

Once the integration is enabled and correctly configured, the student and teacher panels on theirs iScholar interfaces will now display the `Moodle` item in the options menu (left column of the page). By clicking on the item, the student or teacher will be automatically authenticated and redirected to their Moodle page. 

## Deactivating the integration

To disable the **iScholar <> Moodle Authentication** integration, go to the plugin settings page in Moodle and uncheck the `Enabled` option, as shown in the figure below, and click the `Save Changes` button. This procedure will disable integration in both Moodle and your iScholar. 
![Image-11](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image11.png)

Note that this procedure will not uninstall the plugin, only deactivate it. To uninstall the plugin go to `Site administration` / `Plugins` / `Plugins overview`, search for the `iScholar <> Moodle Authentication` plugin and click on `uninstall`. Keep in mind that it will only be possible to uninstall the plugin if no user account is using it as an authentication method, otherwise you must change the authentication method for those accounts first. 

## Important and final considerations 

Please be aware that for a student or teacher to be able to authenticate to Moodle through their iScholar interfaces it is necessary that: 

* The student or teacher has a Moodle account as well as an iScholar account.
* The e-mail address registered in both accounts is the same.
+ The authentication method selected in the Moodle account is `iScholar <> Moodle Authentication` (as illustrated in the figure below). A user's Moodle account can be accessed via the `Site Administration` / `Users` / `Accounts` / `Browse list of users` 
![Image-12](https://github.com/SistemaiScholar/moodle-auth_ischolar/blob/main/docs/image12.png)

## Suported versions

The **iScholar <> Moodle Authentication** plugin has been successfully executed in the following versions:

* `Moodle 3.0.10` with `PHP 5.6.40` and `MySQL 5.7.31`.
* `Moodle 3.1.18` with `PHP 5.6.40` and `MySQL 5.7.31`.
* `Moodle 3.2.9` with `PHP 5.6.40` and `MySQL 5.7.31`.
* `Moodle 3.3.9+` with `PHP 5.6.40` and `MySQL 5.7.31`.
* `Moodle 3.4.9` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.5.18` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.6.10` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.7.2` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.7.9` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.8.9` with `PHP 7.1.33` and `MySQL 5.7.31`.
* `Moodle 3.8.9` with `PHP 7.4.8` and `MySQL 5.7.31`.
* `Moodle 3.9.10+` with `PHP 7.4.8` and `MySQL 5.7.31`.
* `Moodle 3.10.7+` with `PHP 7.4.8` and `MySQL 5.7.31`.
* `Moodle 3.11.3+` with `PHP 7.4.8` and `MySQL 5.7.31`.
* `Moodle 3.11.3+` with `PHP 8.0.12` and `MySQL 5.7.31`.

Moodle versions `3.4.9`, `3.5.18`, `3.6.10`, `3.7.2` e `3.7.9` do not support `PHP 7.4.8`.<br/>
Moodle versions `3.8.9`, `3.9.10+` e `3.10.7+` do not supoort `PHP 8.0.12`.
