# typecho-to-canvas

Easily convert your blog data from Typecho to Canvas blog platform.

# Features

 - The first "Switch to Canvas" program for Typecho
 - Convert data include tags, posts, relationships
 - Loading and parsing config automatically
 - CLI mode
 - ~~Adorable developer~~

# Requirement

 - Operating permission of server
 - PHP version >= 5.6.4
 - Composer
 - PHP PDO extensions for your database type
 - PDO complicant database
 - Patient

# Usage

 Before converting, you must have Typecho and Canvas installed correctly on converting environment.

### Install via Git and convert

 - Clone this repository.
 ```shell
 $ git clone https://github.com/kirainmoe/typecho-to-canvas
 $ cd typecho-to-canvas
 ```
 - Install dependencies. You must have composer worked normally.
 ```shell
 $ composer install
 ```
 - Run **te2cn**, which lies on the root directory of this repository.
 ```shell
 $ php te2cn
 ```
 - Follow the instruction and fill in the config.
 - Enjoy Canvas!

# Q&A

Q: How can I know what caused my failure of converting data?
A: After checking your local environment (eg.permission, database), you can open an issue to tell us the trouble you are facing. We will help you with it.

Q: I don't use **Typecho** but I use **Wordpress**, how can I export my data?
A: You may consider [this](https://github.com/magnetion/wordpress-to-canvas) or using [typecho2wordpress](https://github.com/panxianhai/typecho2wordpress) first before exporting data to Canvas.

# Compatibility

*te2cn* worked perfectly on Typecho 1.0(14.10.10) and Canvas(3.1.0).

# Contribute

Feel free to contribute (Pull requests & issues are welcomed).

# License

GNU General Public License v2.0.
