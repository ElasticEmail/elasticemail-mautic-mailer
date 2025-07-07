# ElasticEmail Mailer Mautic Plugin Installation Guide

## Requirements

- Mautic 5.x (minimum 5.1)
- PHP 8.0 or higher

## Installation Steps

1. **Download the Plugin**
    - Download the plugin package from Github repository
    - put the extracted plugin directory under `plugins` directory

2. **Install dependencies**
    - Install ElasticEmail PHP library using Composer
    - Under Mautic main directory call the following:
    ```sh
    composer require elasticemail/elasticemail-php
    ```

3. **Clear Mautic cache**
    - Clear the Mautic cache to ensure the plugin is loaded correctly.
    - You can do this by running the following command in your Mautic root directory:
     ```sh
     php bin/console cache:clear
     ```

4. **Clear Mautic plugin cache**

    - Clear the Mautic plugin cache to ensure the plugin is loaded correctly.
    - You can do this by running the following command in your Mautic root directory:
     ```sh
     php bin/console mautic:plugins:reload
     ```

5. **Verify Installation**
    - The plugin should appear on the list of available plugins.

    ![Mautic Plugins page](elasticemail-mailer-bundle-plugins.png)

6. **Configure the Plugin**
    - Once installed, navigate to `Configuration` > `Email settings`.
    - Under section `Email DSN` enter the following values:
        - Scheme: `elasticemail+api`
        - Host: `default`
        - User: `your ElasticEmail API Key`
    - Test settings by clicking `Send test email`

    ![Mautic Email configuration page](elasticemail-mailer-bundle-config.png)

## Troubleshooting
- If you encounter any issues, check the Mautic logs located in the `var/logs` directory.
- Ensure that all requirements are met and that there are no conflicts with other plugins.

For more detailed instructions, refer to the official documentation or support resources.