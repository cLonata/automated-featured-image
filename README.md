# Auto Featured Images for WordPress
A simple and straightforward solution to handle featured images for posts in WordPress. This code defines two functions that work as filters to ensure that all posts have a featured image.

## Function 1: afi_fake_thumbnail_id
This function creates a fake thumbnail ID for posts that do not have a featured image set. This helps to bypass any placeholders that may be set up in the theme.

## Function 2: afi_auto_thumbnails
This function automatically sets a featured image for posts that do not have one set. It searches the content of the post for images and uses the first one it finds as the featured image.

## Integration with WordPress
The code uses the WordPress add_filter function to attach these functions to the post_thumbnail_id and post_thumbnail_url filters, respectively.

## Usage
Simply copy the code into your WordPress theme's functions.php file or into a custom plugin and activate it. No further configuration is required, the code will take care of the rest.

## Contribution
Feel free to contribute to this project by submitting bug reports, suggesting new features, or by creating pull requests.

## License

The Automated Featured Image is licensed under the BSD-3 license.
