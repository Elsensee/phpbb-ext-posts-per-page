# "Individual posts per page" extension for phpBB 3.2+

This is the repository for the development of the Individual posts per page Extension.

## Install

1. Download the latest release.
2. Unzip the downloaded release, and change the name of the folder to `postsperpage`.
3. In the `ext` directory of your phpBB board, create a new directory named `elsensee` (if it does not already exist).
4. Copy the `postsperpage` directory to `phpBB/ext/elsensee/` (if done correctly, you'll have the main extension class at (your forum root)/ext/elsensee/postsperpage/ext.php).
5. Navigate in the ACP to `Customise -> Manage extensions`.
6. Look for `Individual posts per page` under the Disabled Extensions list, and click its `Enable` link.
7. Set up and configure this extension by navigating in the ACP to `Overview` -> `Post settings`.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Individual posts per page` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/elsensee/postsperpage` directory.

## Support

* Report bugs and other issues to the [Issue Tracker](https://github.com/Elsensee/phpbb-ext-posts-per-page/issues).

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
