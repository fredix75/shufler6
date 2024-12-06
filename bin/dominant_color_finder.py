import sys
import requests
import binascii
import struct
from PIL import Image
import os
os.environ['OPENBLAS_NUM_THREADS'] = '1'
from io import BytesIO
import scipy.cluster
import sklearn.cluster
import numpy
from PIL import Image
import binascii

image_path = sys.argv[1]
response = requests.get(image_path)
image = Image.open(BytesIO(response.content))
image = image.resize((150, 150))      # optional, to reduce time
ar = numpy.asarray(image)
shape = ar.shape
ar = ar.reshape(numpy.prod(shape[:2]), shape[2]).astype(float)

kmeans = sklearn.cluster.MiniBatchKMeans(
    n_clusters=10,
    init="k-means++",
    max_iter=20,
    random_state=1000
).fit(ar)
codes = kmeans.cluster_centers_

vecs, _dist = scipy.cluster.vq.vq(ar, codes)         # assign codes
counts, _bins = numpy.histogram(vecs, len(codes))    # count occurrences

colors = []
for index in numpy.argsort(counts)[::-1]:
    colors.append(tuple([int(code) for code in codes[index]]))

colour = binascii.hexlify(bytearray(int(c) for c in colors)).decode('ascii')
print('#%s' % colour)