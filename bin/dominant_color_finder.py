import sys
import requests
import binascii
import struct
from PIL import Image
import os
os.environ['OPENBLAS_NUM_THREADS'] = '1'

import numpy as np
import scipy
import scipy.misc
import scipy.cluster
from io import BytesIO

NUM_CLUSTERS = 5

image_path = sys.argv[1]
response = requests.get(image_path)
img = Image.open(BytesIO(response.content))
im = img.resize((150, 150))      # optional, to reduce time
ar = np.asarray(im)
shape = ar.shape
ar = ar.reshape(np.prod(shape[:2]), shape[2]).astype(float)
codes, dist = scipy.cluster.vq.kmeans(ar, NUM_CLUSTERS)
vecs, dist = scipy.cluster.vq.vq(ar, codes)         # assign codes
counts, bins = scipy.histogram(vecs, len(codes))    # count occurrences
index_max = scipy.argmax(counts)                    # find most frequent
peak = codes[index_max]
colour = binascii.hexlify(bytearray(int(c) for c in peak)).decode('ascii')
print('#%s' % colour)