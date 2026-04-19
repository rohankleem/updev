// File for your custom JavaScript
console.log("custom theme js code");

// 1. Import your SVGs 
import gen017Svg from '../vendor/duotone-icons/gen/gen017.svg';
import gen018Svg from '../vendor/duotone-icons/gen/gen018.svg';
import art009Svg from '../vendor/duotone-icons/art/art009.svg';
import ecm003Svg from '../vendor/duotone-icons/ecm/ecm003.svg';
import gra010Svg from '../vendor/duotone-icons/gra/gra010.svg';
import gra012Svg from '../vendor/duotone-icons/gra/gra012.svg';
import art002Svg from '../vendor/duotone-icons/art/art002.svg';
import gen020Svg from '../vendor/duotone-icons/gen/gen020.svg';
import map007Svg from '../vendor/duotone-icons/map/map007.svg';
import gen004Svg from '../vendor/duotone-icons/gen/gen004.svg';
import arr031Svg from '../vendor/duotone-icons/arr/arr031.svg';
import teh001Svg from '../vendor/duotone-icons/teh/teh001.svg';
import gen012Svg from '../vendor/duotone-icons/gen/gen012.svg';
import cod006Svg from '../vendor/duotone-icons/cod/cod006.svg';
import fil021Svg from '../vendor/duotone-icons/fil/fil021.svg';
import gen002Svg from '../vendor/duotone-icons/gen/gen002.svg';
import cod007Svg from '../vendor/duotone-icons/cod/cod007.svg';
import txt001Svg from '../vendor/duotone-icons/txt/txt001.svg';

// 2. Store your imported SVGs and their respective container IDs in an array.
const icons = [
    { svg: gen017Svg, container: 'gen017Svg' }, // cube
    { svg: gen018Svg, container: 'gen018Svg' }, // map marker
    { svg: art009Svg, container: 'art009Svg' }, // curl up graph
    { svg: ecm003Svg, container: 'ecm003Svg' }, // percentage tag
    { svg: gra010Svg, container: 'gra010Svg' }, // pie chart
    { svg: gra012Svg, container: 'gra012Svg' }, // graph
    { svg: art002Svg, container: 'art002Svg' }, // measuring sticks
    { svg: gen020Svg, container: 'gen020Svg' }, // trophy
    { svg: map007Svg, container: 'map007Svg' }, // target
    { svg: gen004Svg, container: 'gen004Svg' }, // magnifying glass
    { svg: arr031Svg, container: 'arr031Svg' }, // circuit arrows
    { svg: teh001Svg, container: 'teh001Svg' }, // connecting chip
    { svg: gen012Svg, container: 'gen012Svg' }, // hour glass
    { svg: cod006Svg, container: 'cod006Svg' }, // click arrow
    { svg: fil021Svg, container: 'fil021Svg' }, // download
    { svg: gen002Svg, container: 'gen002Svg' }, // rocket
    { svg: cod007Svg, container: 'cod007Svg' }, // link
    { svg: txt001Svg, container: 'txt001Svg' }, // link
    // ... add more icons as needed
];

document.addEventListener("DOMContentLoaded", function() {
    // 3. Loop through the array and inject the SVG content into the containers.
    icons.forEach(icon => {
        const containerElements = document.getElementsByClassName(icon.container);
        if (containerElements.length > 0) {
            for (let i = 0; i < containerElements.length; i++) {
                containerElements[i].innerHTML = icon.svg;
            }
        }
    });
});
