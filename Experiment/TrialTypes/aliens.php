<?php
/**
 * $features should be in the following order:
 * 0: body
 * 1: hands
 * 2: tail
 * 3: feet
 * 4: hair
 * 5: mouth
 * 6: eyes
 * 7: brightness
 */
function create_alien($features, $feature_mask = []) {
    static  $id = 0;
    
    echo "<canvas class='alien_$id' width=300 height=500></canvas>";
    
    ?><script>
        window.addEventListener("load", function() {
            let alien_canvas = document.querySelector(".alien_<?= $id ?>");
            let features = <?= json_encode($features) ?>;
            let feature_mask = <?= json_encode($feature_mask) ?>;
            
            window.alien_assembler.draw(alien_canvas, features, feature_mask);
        });
    </script><?php
    
    ++$id;
}

?>

<style>
    #alien_spritesheet1, #alien_spritesheet2 { display: none; }
</style>

<img id="alien_spritesheet1" src="../Experiment/Images/people_alien1_resized.png">
<img id="alien_spritesheet2" src="../Experiment/Images/people_alien2_resized.png">

<script>
var alien_assembler = {
    data: {
        a1: {
            body: [0, 351, 214, 357, 50, 55, 214, 357],
            tail: [
                [294, 700,129,65,167,258,129,65],
                [576, 700,129,65,157,256,129,65],
                [831, 702,129,65,167,249,129,65],
                [1118,701,129,65,171,267,129,65],
                [1424,701,129,65,169,260,129,65],
            ],
            hands: [
                [254, 771,217,100,46,281,217,100],
                [527, 771,217,100,44,283,217,100],
                [776, 772,217,100,43,282,217,100],
                [1072,771,217,100,46,282,217,100],
                [1381,772,217,100,46,277,217,100],
            ],
            feet: [
                [268, 876,200,118,61,348,200,118],
                [538, 877,200,118,57,356,200,118],
                [808, 876,200,118,59,364,200,118],
                [1076,878,200,118,57,366,200,118],
                [1381,877,200,118,55,368,200,118],
            ],
            hair: [
                [256, 469,150,83,25,35,150,83],
                [539, 470,150,83,25,20,150,83],
                [802, 470,150,83,25,27,150,83],
                [1109,472,150,81,30,22,150,83],
                [1405,470,150,83,25,11,150,83],
            ],
            mouth: [
                [294, 654,115,43,105,165,115,43],
                [551, 653,115,43,96,165,115,43],
                [832, 655,115,43,96,165,115,43],
                [1117,653,115,43,96,165,115,43],
                [1436,654,115,42,96,161,115,42],
            ],
            eye: [
                [278, 555,150,89,80,90,150,89],
                [553, 557,150,89,80,94,150,89],
                [821, 557,150,89,80,90,150,89],
                [1085,557,216,89,47,90,216,89],
                [1413,558,164,89,75,81,164,89],
            ],
        },
        a2: {
            body: [0,355,193,248,54,84,193,248],
            tail: [
                [312, 644,136,88,91,231,136,89],
                [595, 644,136,88,91,231,136,89],
                [860, 644,136,88,83,242,136,89],
                [1120,644,136,88,94,242,136,89],
                [1432,644,136,88,87,255,136,89],
            ],
            hands: [
                [244, 734,267,158,12,175,267,158],
                [519, 735,267,158, 9,180,267,158],
                [789, 734,267,158,13,180,267,158],
                [1081,735,267,158,11,180,267,158],
                [1372,734,267,158,15,188,267,158],
            ],
            feet: [
                [264, 899,241,90,21,310,241,90],
                [542, 899,241,90, 3,309,241,90],
                [815, 899,241,90,21,312,241,90],
                [1084,899,241,90,23,309,241,90],
                [1382,899,241,90,21,310,241,90],
            ],
            hair: [
                [301, 389,151,94,73,28,151,94],
                [587, 389,151,94,73,30,151,94],
                [847, 389,151,94,78,21,151,94],
                [1129,389,151,94,73,25,151,94],
                [1448,389,151,94,81,18,151,94],
            ],
            mouth: [
                [263, 561,240,80,28,149,240,80],
                [535, 561,240,80,32,173,240,80],
                [807, 561,240,80,28,157,240,80],
                [1093,561,240,80,43,162,240,80],
                [1380,561,240,80,36,162,240,80],
            ],
            eye: [
                [289, 488,182,70,62,96,182,70],
                [558, 488,182,70,60,98,182,70],
                [829, 488,182,70,63,100,182,70],
                [1101,488,182,70,62,96,182,70],
                [1421,488,182,70,62,96,182,70],
            ],
        },
    },
    
    draw: function(canvas, features, feature_mask) {
        features = features.map(e => Number(e));
        
        if (!(7 in features)) features[7] = 10; // default value of 10, representing no change (since value is divided by 10)
        
        feature_mask = this.get_correct_feature_mask(feature_mask, features);
        
        let context = this.get_cleared_context(canvas);
        let [image, alien] = this.get_image_and_alien_data(features[0]);
        let filter = this.get_filter(features[7]);
        
        if (feature_mask[1]) context.drawImage(image, ...alien.hands[features[1]]);
        if (feature_mask[0]) {
            context.filter = filter;
            context.drawImage(image, ...alien.body);
            context.filter = "none";
        }
        if (feature_mask[2]) context.drawImage(image, ...alien.tail [features[2]]);
        if (feature_mask[3]) context.drawImage(image, ...alien.feet [features[3]]);
        if (feature_mask[4]) context.drawImage(image, ...alien.hair [features[4]]);
        if (feature_mask[5]) context.drawImage(image, ...alien.mouth[features[5]]);
        if (feature_mask[6]) context.drawImage(image, ...alien.eye  [features[6]]);
    },
    
    get_cleared_context: function(canvas) {
        let context = canvas.getContext("2d");
        context.clearRect(0, 0, canvas.width, canvas.height);
        return context;
    },
    
    get_image_and_alien_data: function(alien_type) {
        var image, alien;
        
        if (alien_type > 0) {
            image = document.getElementById("alien_spritesheet2");
            alien = this.data.a2;
        } else {
            image = document.getElementById("alien_spritesheet1");
            alien = this.data.a1;
        }
        
        return [image, alien];
    },
    
    get_filter: function(filter_val) {
        return "brightness("
             + (filter_val / 10)
             + ")";
    },
    
    get_correct_feature_mask: function(feature_mask, features) {
        if (!Array.isArray(feature_mask)) {
            feature_mask = [];
        }
        
        for (let i = 0; i < features.length; ++i) {
            if (typeof feature_mask[i] === "undefined") {
                feature_mask[i] = 1;
            }
        }
        
        return feature_mask.map(e => Number(e));
    },
};
</script>
