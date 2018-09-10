# RELLAX

Rellax is a buttery smooth, super lightweight (1021bytes gzipped), vanilla javascript parallax library. **Update:** Rellax now works on mobile (v1.0.0). 

* [Demo Website](https://dixonandmoe.com/rellax/)

Have any suggestions or feedback? Reach out [@dixonandmoe](https://twitter.com/dixonandmoe)

## Getting Started
`npm install rellax --save` or if you're old school like us download and insert `rellax.min.js`

```html
<div class="rellax">
  I’m that default chill speed of "-2"
</div>
<div class="rellax" data-rellax-speed="7">
  I’m super fast!!
</div>
<div class="rellax" data-rellax-speed="-4">
  I’m extra slow and smooth
</div>

<script src="rellax.min.js"></script>
<script>
  // Accepts any class name
  var rellax = new Rellax('.rellax');
</script>
```
```html
<script>
  // Also can pass in optional settings block
  var rellax = new Rellax('.rellax');
</script>
```
## Features

### Centering
After some fantastic work from [@p-realinho](https://github.com/p-realinho), we just released the ability to center parallax elements in your viewport! We'll be building a nice demo website, but for now check out the tests folder for several examples of how it works.

There's two ways to implement centering, either on specific elements or as a global option.
```html
<div class="rellax" data-rellax-percentage="0.5">
  I’m that default chill speed of "-2" and "centered"
</div>
<div class="rellax" data-rellax-speed="7" data-rellax-percentage="0.5">
  I’m super fast!! And super centered!!
</div>
<div class="rellax" data-rellax-speed="-4" data-rellax-percentage="0.5">
  I’m extra slow and smooth, and hella centered.
</div>
```
```html
<script>
  // Center all the things!
  var rellax = new Rellax('.rellax', {
    center: true
  });
</script>
```
### Destroy
```html
<script>
  // Start Rellax
  var rellax = new Rellax('.rellax');
  
  // End Rellax and reset parallax elements to their original positions
  rellax.destroy();
</script>
```
## In the Wild
If you're using Rellax in production, we'd love to list you here! Let us know: moe@dixonandmoe.com
- [Lorem Ipsum Generator](https://loremipsumgenerator.com/)
- [Alex Bailon Portfolio](http://www.iambailon.com/)

## Development
In the spirit of lightweight javascript, the build processes (thus far) is lightweight also.

1. Open demo.html
2. Make code changes and refresh browser
3. Once feature is finished or bug fixed, use [jshint](http://jshint.com/) to lint code
4. Fix lint issues then use [Google Closure Compiler](https://closure-compiler.appspot.com/home) to minify
5. 🍻

