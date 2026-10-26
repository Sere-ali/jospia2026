#!/usr/bin/env python3
"""Détection du visage principal d'une photo. Usage : face.py image  ->  JSON {"x","y","w","h"} (fractions 0..1) ou {}"""
import sys, json, os
try:
    import cv2, numpy as np
    img = cv2.imread(sys.argv[1])
    if img is None: print('{}'); sys.exit(0)
    H, W = img.shape[:2]
    s = 960.0 / max(W, H)
    if s < 1: img = cv2.resize(img, (int(W * s), int(H * s)), interpolation=cv2.INTER_AREA)
    h, w = img.shape[:2]
    best = None
    model = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'yunet.onnx')
    casc = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    def yunet(im, thr):
        ih, iw = im.shape[:2]
        det = cv2.FaceDetectorYN.create(model, '', (iw, ih), thr, 0.3, 5000)
        _, faces = det.detect(im)
        return [(f[0], f[1], f[2], f[3], f[14]) for f in faces] if faces is not None else []

    def haar(im):
        ih, iw = im.shape[:2]
        g = cv2.equalizeHist(cv2.cvtColor(im, cv2.COLOR_BGR2GRAY))
        r = casc.detectMultiScale(g, 1.1, 5, minSize=(max(24, iw // 20), max(24, iw // 20)))
        return [(x, y, bw, bh, 0.5) for (x, y, bw, bh) in r]

    # fenêtres : image entière, puis moitiés / tiers agrandis (visages petits : photos en pied, groupes)
    fen = [(0, 0, 1, 1)]
    for fy0, fy1 in ((0, .5), (0, .66), (.2, .8), (.4, 1)):
        for fx0, fx1 in ((0, 1), (0, .6), (.4, 1), (.2, .8)):
            fen.append((fx0, fy0, fx1, fy1))
    use_yunet = os.path.isfile(model) and hasattr(cv2, 'FaceDetectorYN')
    cands = []
    for (fx0, fy0, fx1, fy1) in fen:
        x0, y0, x1, y1 = int(fx0 * w), int(fy0 * h), int(fx1 * w), int(fy1 * h)
        sub = img[y0:y1, x0:x1]
        if sub.size == 0: continue
        sh, sw = sub.shape[:2]
        k = min(2.5, 640.0 / max(sw, sh)) if max(sw, sh) < 640 else 1.0
        if k != 1.0: sub = cv2.resize(sub, (int(sw * k), int(sh * k)), interpolation=cv2.INTER_CUBIC)
        res = []
        if use_yunet:
            for thr in (0.6, 0.4):
                res = yunet(sub, thr)
                if res: break
        if not res: res = haar(sub)
        for (x, y, bw, bh, sc) in res:
            if bw <= 0 or bh <= 0: continue
            cands.append(((x / k + x0), (y / k + y0), bw / k, bh / k, sc))
        if cands and (fx0, fy0, fx1, fy1) == (0, 0, 1, 1): break
    if cands:
        # priorité : visage le plus grand parmi ceux détectés avec bonne confiance
        cands.sort(key=lambda r: (r[4] >= 0.5, r[2] * r[3]), reverse=True)
        best = cands[0][:4]
    if best is None: print('{}'); sys.exit(0)
    x, y, bw, bh = [float(v) for v in best]
    print(json.dumps({'x': round(max(0, x) / w, 4), 'y': round(max(0, y) / h, 4), 'w': round(bw / w, 4), 'h': round(bh / h, 4)}))
except Exception:
    print('{}')
