
(defparameter *board-size* 8 "Chessboard dimension")


(defstruct search-state
  queens
  g-score
  h-score
  f-score
  parent)


(defun safe-position-p (row col queens)
  (dolist (queen queens)
    (let ((q-row (first queen))
          (q-col (second queen)))
      (when (or (= row q-row)
                (= col q-col)
                (= (abs (- row q-row))
                   (abs (- col q-col))))
        (return-from safe-position-p nil))))
  t)


(defun print-board (queens &optional (step-num nil))
  (format t "~%~%")
  (when step-num
    (format t "Step ~A:~%" step-num))
  
  (format t "   ")
  (dotimes (col *board-size*) 
    (format t "~A " col))
  (format t "~%   ")
  (dotimes (col *board-size*)
    (format t "- "))
  
  (dotimes (row *board-size*)
    (format t "~%~A |" row)
    (dotimes (col *board-size*)
      (format t " ~A" (if (find (list row col) queens :test #'equal) "Q" "."))))
  
  (format t "~%~%Queens at positions: ")
  (loop for queen in (reverse queens)
        for pos from 1
        do (format t "~%Queen #~A: (row:~A, col:~A)" 
                   pos (first queen) (second queen))))


(defun heuristic (queens)
  (- *board-size* (length queens)))


(defun generate-astar-successors (state)
  (let* ((queens (search-state-queens state))
         (used-rows (mapcar #'first queens))
         (next-row (loop for row from 0 below *board-size*
                        when (not (member row used-rows)) return row))
         successors)
    (when next-row
      (dotimes (col *board-size*)
        (when (safe-position-p next-row col queens)
          (let* ((new-queens (cons (list next-row col) queens))
                 (new-g (1+ (search-state-g-score state)))
                 (new-h (heuristic new-queens))
                 (new-f (+ new-g new-h)))
            (push (make-search-state :queens new-queens
                                   :g-score new-g
                                   :h-score new-h
                                   :f-score new-f
                                   :parent state)
                  successors)))))
    successors))


(defun insert-by-priority (state queue)
  (if (null queue)
      (list state)
      (if (<= (search-state-f-score state) (search-state-f-score (car queue)))
          (cons state queue)
          (cons (car queue) (insert-by-priority state (cdr queue))))))


(defun astar-solve (initial-queens)
  (let ((open-list (list (make-search-state :queens initial-queens
                                           :g-score 0
                                           :h-score (heuristic initial-queens)
                                           :f-score (heuristic initial-queens))))
        (closed-list nil)
        (step 0))
    (loop while open-list do
          (let ((current (pop open-list)))
            (incf step)
            (format t "~%~%Exploring step ~A:" step)
            (print-board (search-state-queens current) step)
            (cond
              ((= (length (search-state-queens current)) *board-size*)
               (format t "~%~%Final solution found at step ~A!" step)
               (return-from astar-solve current))
              (t (push current closed-list)
                 (dolist (successor (generate-astar-successors current))
                   (unless (find (search-state-queens successor) closed-list
                               :key #'search-state-queens :test #'equal)
                     (setf open-list (insert-by-priority successor open-list))))))))
    nil))


(defun dfs-solve (initial-queens)
  (let ((step 0))
    (labels ((next-available-row (queens)
               (loop for row from 0 below *board-size*
                     when (not (find row (mapcar #'first queens))) return row))
             (dfs-helper (queens)
               (incf step)
               (format t "~%~%Trying step ~A:" step)
               (print-board queens step)
               (if (= (length queens) *board-size*)
                   queens
                   (let ((target-row (next-available-row queens)))
                     (loop for col from 0 below *board-size*
                           when (safe-position-p target-row col queens)
                           do (let ((solution (dfs-helper (cons (list target-row col) queens))))
                                (when solution 
                                  (return-from dfs-helper solution))))
                     nil))))
      (dfs-helper initial-queens))))


(defun read-initial-position ()
  (labels ((read-valid-coordinate (prompt)
             (loop 
               (format t prompt)
               (let ((input (read-line)))
                 (cond ((not (every #'digit-char-p input)) 
                        (format t "Numbers only!~%"))
                       (t (let ((num (parse-integer input)))
                            (if (<= 0 num (1- *board-size*)) 
                                (return num)
                                (format t "Must be 0-~A!~%" (1- *board-size*))))))))))
    (list (read-valid-coordinate "Start row (0-7): ")
          (read-valid-coordinate "Start column (0-7): "))))


(defun select-algorithm ()
  (format t "~%~%Select algorithm:")
  (format t "~%1) DFS (Depth-First Search)")
  (format t "~%2) A* (A-Star Search)")
  (loop 
    (format t "~%Choice (1/2): ")
    (let ((input (read-line)))
      (cond
        ((string= input "1") (return 'dfs))
        ((string= input "2") (return 'astar))
        (t (format t "Invalid choice! Please enter 1 or 2.~%"))))))


(defun main ()
  (format t "~%~%=== 8-Queens Solver with Step Visualization ===")
  (format t "~%Board size: ~Ax~A~%" *board-size* *board-size*)
  (let* ((initial-queen (read-initial-position))
         (algorithm (select-algorithm))
         (start-real (get-internal-real-time))
         (start-run (get-internal-run-time))
         (solution (ecase algorithm
                     (dfs (dfs-solve (list initial-queen)))
                     (astar (let ((fs (astar-solve (list initial-queen)))) 
                              (when fs (search-state-queens fs))))))
         (end-real (get-internal-real-time))
         (end-run (get-internal-run-time)))
    
    (format t "~%~%Performance Metrics:")
    (format t "~%- Real time taken: ~5$ seconds" 
            (/ (- end-real start-real) internal-time-units-per-second))
    (format t "~%- CPU time taken: ~5$ seconds" 
            (/ (- end-run start-run) internal-time-units-per-second))
    
    (format t "~%~%Memory Status:")
    (room nil)  ; Show memory allocation statistics
    
    (if solution
        (progn 
          (format t "~%~%Final Solution:")
          (print-board solution)
          (format t "~%~%Summary:")
          (format t "~%- Total queens placed: ~A" (length solution))
          (format t "~%- Starting position: (~A,~A)" 
                  (first (first (last solution))) 
                  (second (first (last solution))))
          (format t "~%- Solution found!"))
        (format t "~%~%No solution exists!"))))


(main)