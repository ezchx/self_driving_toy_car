import sys
import json

# initialize variables

room = [[0,0,0,0,0],
        [0,1,1,1,0],
        [0,0,0,0,0]]


init = json.loads(sys.argv[1])
#init = [0, 0]
goal = [0, len(room[0])-1]

cost = 1

delta = [[-1, 0], # go up
         [ 0,-1], # go left
         [ 1, 0], # go down
         [ 0, 1]] # go right

delta_name = ['^', '<', 'v', '>']

def search(room,init,goal,cost):
    
    g = 0 # number of steps
    open_count = 0
    expand = [[-1 for col in range(len(room[0]))] for row in range(len(room))]
    arrows = [["o" for col in range(len(room[0]))] for row in range(len(room))]
    open_list = [[g, init[0], init[1]]]
    expand[init[0]][init[1]] = open_count
    closed_list = []
    current_cell = []
    next_cell = [0,0,0]
    search = 1
    
    while search:

        # select cell from open list with min g value, add to closed list, and delete from open list
        min_index = open_list.index(min(open_list))
        current_cell = open_list[min_index]
        closed_list.append(open_list[min_index])
        del open_list[min_index]

        # expand to next cell(s)
        for i in range(len(delta)):

            cell = "good"
            next_cell = [0,0,0]
            next_cell[0] = current_cell[0] + cost
            next_cell[1] = current_cell[1] + delta[i][0]
            next_cell[2] = current_cell[2] + delta[i][1]
            
            if (next_cell[1] == goal[0] and next_cell[2] == goal[1]): # check if goal
                path = next_cell # final (goal) cell with g value
                arrows[next_cell[1]][next_cell[2]] = "*"
                search = 0
           
            if ( next_cell[1] >= 0 and next_cell[1] <= (len(room)-1) and next_cell[2] >= 0 and next_cell[2] <= (len(room[0])-1) ): # not off the grid
                if room[next_cell[1]][next_cell[2]] != 1: # not a wall

                    for j in range(len(open_list)): # check open list
                        if (open_list[j][1] == next_cell[1] and open_list[j][2] == next_cell[2]):
                            cell = "bad"
                    
                    for j in range(len(closed_list)): # check closed list
                        if (closed_list[j][1] == next_cell[1] and closed_list[j][2] == next_cell[2]):
                            cell = "bad"

                    if cell == "good": # add to open list
                        open_list.append(next_cell)
                        open_count += 1
                        expand[next_cell[1]][next_cell[2]] = open_count

        # fail if no more open cells
        if len(open_list) == 0:
            path = "fail"
            search = 0

    # backtrack by open count
    this_cell = goal
    for i in range(open_count-1,-1,-1):
        prior_cell = [[x,y] for x, row in enumerate(expand) for y, z in enumerate(row) if z == i]
        y_var = this_cell[0]-prior_cell[0][0]
        x_var = this_cell[1]-prior_cell[0][1]
        if abs(y_var) + abs(x_var) == 1:
            arrows[prior_cell[0][0]][prior_cell[0][1]] = delta_name[delta.index([y_var, x_var])]
            this_cell = [prior_cell[0][0], prior_cell[0][1]]
    
    return arrows


dodo = search(room,init,goal,cost)
if (cmp(init, goal) == 0):
  dodo[init[0]][init[1]] = "*"

# output results
print json.dumps(dodo)
