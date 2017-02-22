import math
import sys
import json

# initialize variables


x = 1./12

p = [[x,x,x,x,x],
     [x,0,0,0,x],
     [x,x,x,x,x]]


#room = [
#[[0,1,0,0,0,0,0,0], [0,1,0,0,0,1,0,0], [0,1,0,0,0,1,0,0], [0,1,0,0,0,1,0,0], [0,1,0,1,0,0,0,0]],
#[[0,0,0,1,0,0,0,0], [-99, -99, -99, -99, -99, -99, -99, -99], [-99, -99, -99, -99, -99, -99, -99, -99], [-99, -99, -99, -99, -99, -99, -99, -99], [0,0,0,1,0,0,0,1]],
#[[0,0,0,0,0,0,0,0], [0,1,0,0,0,0,0,0], [0,1,0,0,0,1,0,0], [0,1,0,0,0,0,0,0], [0,0,0,1,0,1,0,0]]]

#room = [
[[1,1,1,0,0,0,0,0], [1,1,1,0,1,1,1,0], [1,1,1,0,1,1,1,0], [1,1,1,0,1,1,1,0], [1,1,1,1,1,0,0,0]],
[[0,0,1,1,1,0,0,0], [-99, -99, -99, -99, -99, -99, -99, -99], [-99, -99, -99, -99, -99, -99, -99, -99], [-99, -99, -99, -99, -99, -99, -99, -99], [1,0,1,1,1,0,1,1]],
[[0,0,0,0,0,0,0,0], [1,1,1,0,0,0,0,0], [1,1,1,0,1,1,1,0], [1,1,1,0,0,0,0,0], [0,0,1,1,1,1,1,0]]]


#measurements = [[1,0,0,1,1,0,1,1],[0,0,0,0,1,0,1,1],[0,0,0,0,1,1,0,0]]
#motions = [[-1,0], [0,1], [0, 0]] # row (N/S), column (E/W) this says W & S

measurements = json.loads(sys.argv[1])
motions = json.loads(sys.argv[2])

pHit = 1.0
pMiss = 0.0
pExact = 1.0
pOvershoot = 0.0
pUndershoot = 0.0



def look_8_ways(i, j, Z):
    hit = float(0)
    
    for l in range(8):

        if (room[i][j][l] != -99):
          hit += room[i][j][l] * Z[l]
    
    return hit


def sense(p, Z):
    q = [[0 for i in range(len(p[0]))] for j in range(len(p[:]))]
    for i in range(len(p[:])): # rows 
        for j in range(len(p[0])): # cols
            hit = look_8_ways(i, j, Z)
            q[i][j] = p[i][j] * (hit * pHit + (1-hit) * pMiss)
    s = sum(map(sum, q))

    for i in range(len(q[:])): # rows
        for j in range(len(q[0])): # cols
            q[i][j] = math.ceil((q[i][j] / s)*1000)/1000
    return q


def move(p, U):
    q = p

    # row move (N/S)
    if U[0] != 0:
        q = [[0 for i in range(len(p[0]))] for j in range(len(p[:]))]
        for i in range(len(p[:])): # rows = 7
            for j in range(len(p[0])): # cols = 12
                if room[i][j] != 1:
                    s = pExact * p[(i-U[0]) % len(p[:])][j]
                    s = s + pOvershoot * p[(i-U[0]-1) % len(p[:])][j]
                    s = s + pUndershoot * p[(i-U[0]+1) % len(p[:])][j]
                    q[i][j] = math.ceil((s)*1000)/1000

    # column move (E/W)
    if U[1] != 0:
        q = [[0 for i in range(len(p[0]))] for j in range(len(p[:]))]
        for i in range(len(p[:])): # rows = 7
            for j in range(len(p[0])): # cols = 12
                if room[i][j] != 1:
                    s = pExact * p[i][(j-U[1]) % len(p[0])]
                    s = s + pOvershoot * p[i][(j-U[1]-1) % len(p[0])]
                    s = s + pUndershoot * p[i][(j-U[1]+1) % len(p[0])]
                    q[i][j] = math.ceil((s)*1000)/1000
    return q


# main loop
for k in range(len(measurements)):
    p = sense(p, measurements[k]) # this updates the p matrix as you sense.
    p = move(p, motions[k]) # this updates the p matrix as you move.


# output results
print json.dumps(p)
