import Cropper from "cropperjs";

/* The site shows the photo in a 4:5 frame; 800×1000 stays sharp on high-density screens. */
const ASPECT_RATIO = 4 / 5;
const OUTPUT = { width: 800, height: 1000 };

/* A fixed-ratio frame the owner moves and resizes over a still image. */
const TEMPLATE =
    '<cropper-canvas background style="height: 100%">' +
    '<cropper-image initial-center-size="contain"></cropper-image>' +
    '<cropper-shade hidden></cropper-shade>' +
    `<cropper-selection initial-coverage="0.8" aspect-ratio="${ASPECT_RATIO}" movable resizable outlined>` +
    '<cropper-grid role="grid" bordered covered></cropper-grid>' +
    '<cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>' +
    '<cropper-handle action="ne-resize"></cropper-handle>' +
    '<cropper-handle action="nw-resize"></cropper-handle>' +
    '<cropper-handle action="se-resize"></cropper-handle>' +
    '<cropper-handle action="sw-resize"></cropper-handle>' +
    "</cropper-selection>" +
    "</cropper-canvas>";

/* A data: URL, because the content security policy does not allow blob: images. */
function readAsDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsDataURL(file);
    });
}

function contains(outer, inner) {
    return (
        inner.x >= outer.x &&
        inner.y >= outer.y &&
        inner.x + inner.width <= outer.x + outer.width &&
        inner.y + inner.height <= outer.y + outer.height
    );
}

export async function openPhotoCropper(file, stage) {
    const image = new Image();
    image.alt = "Photo to crop";
    image.src = await readAsDataUrl(file);
    await image.decode();

    const cropper = new Cropper(image, { container: stage, template: TEMPLATE });
    const canvas = cropper.getCropperCanvas();
    const selection = cropper.getCropperSelection();
    const picture = cropper.getCropperImage();

    await picture.$ready();

    // Keep the frame on the picture, so the crop never includes empty canvas.
    selection.addEventListener("change", (event) => {
        const canvasRect = canvas.getBoundingClientRect();
        const pictureRect = picture.getBoundingClientRect();
        const bounds = {
            x: pictureRect.left - canvasRect.left,
            y: pictureRect.top - canvasRect.top,
            width: pictureRect.width,
            height: pictureRect.height,
        };

        if (!contains(bounds, event.detail)) {
            event.preventDefault();
        }
    });

    return {
        async toBlob() {
            const result = await selection.$toCanvas({
                ...OUTPUT,
                // White behind transparent PNGs, which JPEG would otherwise turn black.
                beforeDraw(context, target) {
                    context.fillStyle = "#ffffff";
                    context.fillRect(0, 0, target.width, target.height);
                },
            });

            return new Promise((resolve) => result.toBlob(resolve, "image/jpeg", 0.9));
        },
        destroy() {
            cropper.destroy();
            stage.replaceChildren();
        },
    };
}
