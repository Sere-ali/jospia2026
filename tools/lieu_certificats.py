"""Remplace le lieu (« LA PERRUCHE d'Anyama ») par le nom de l'école sur les deux modèles de certificat.
Part des originaux de tools/modeles_originaux/ et écrit dans assets/img/modeles/."""
import sys
from PIL import Image, ImageDraw, ImageFont
import numpy as np

LIEU = "Collège privé Henriette Dagri-Diabaté d’Anyama,"
FONTS = 'assets/fonts/ttf/tinos-latin-%s-normal.ttf'
SS = 4  # suréchantillonnage

CONFIG = [
    # fichier, (x début effacement, x fin), (ligne propre au-dessus, ligne propre en dessous), haut du texte, taille, centre x, largeur max
    dict(f='certificat.jpg', x=(136, 1186), clean=(532, 568), top=537, size=27.0, cx=660, maxw=1060),
    dict(f='certificat_seminariste.jpg', x=(70, 1090), clean=(468, 502), top=474, size=23.7, cx=572, maxw=930),
]

def run(c):
    im = Image.open('tools/modeles_originaux/' + c['f']).convert('RGB')
    a = np.array(im).astype(float)
    y0, y1 = c['clean']
    x0, x1 = c['x']
    # 1. effacer la ligne en interpolant verticalement le fond propre
    for x in range(x0, x1):
        top, bot = a[y0, x].copy(), a[y1, x].copy()
        for y in range(y0 + 1, y1):
            t = (y - y0) / (y1 - y0)
            a[y, x] = top * (1 - t) + bot * t
    im = Image.fromarray(a.clip(0, 255).astype('uint8'))

    # 2. écrire la nouvelle ligne (gras pour la date et le lieu)
    size = c['size']
    def mesure(s):
        fr = ImageFont.truetype(FONTS % '400', s); fb = ImageFont.truetype(FONTS % '700', s)
        return fr.getlength('du ') + fb.getlength('22 au 28 décembre 2026 au ' + LIEU) + fr.getlength(' sous le thème central :')
    while mesure(size) > c['maxw']:
        size -= 0.1
    big = ImageFont.truetype(FONTS % '700', size * SS); reg = ImageFont.truetype(FONTS % '400', size * SS)
    total = mesure(size)
    W, H = im.size
    layer = Image.new('L', (W * SS, 80 * SS), 0)
    d = ImageDraw.Draw(layer)
    base = round((c['top'] + 0.683 * c['size']) * SS) - c['top'] * SS + 20 * SS  # ligne de base dans le calque
    x = (c['cx'] - total / 2) * SS
    for txt, fnt in [('du ', reg), ('22 au 28 décembre 2026 au ' + LIEU, big), (' sous le thème central :', reg)]:
        d.text((x, base), txt, font=fnt, fill=255, anchor='ls')
        x += fnt.getlength(txt)
    layer = layer.resize((W, 80), Image.LANCZOS)
    noir = Image.new('RGB', (W, 80), (12, 12, 12))
    zone = im.crop((0, c['top'] - 20, W, c['top'] + 60))
    zone.paste(noir, (0, 0), layer)
    im.paste(zone, (0, c['top'] - 20))
    im.save('assets/img/modeles/' + c['f'], quality=95, subsampling=0, optimize=True)
    print(c['f'], 'taille finale', round(size, 1))

for c in CONFIG:
    run(c)
