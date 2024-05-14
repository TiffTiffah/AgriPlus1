def calculate_grade_point(raw_score):
    if raw_score >= 75:
        return 4.00
    elif raw_score >= 70:
        return 3.75
    elif raw_score >= 65:
        return 3.50
    elif raw_score >= 60:
        return 3.00
    elif raw_score >= 55:
        return 2.75
    elif raw_score >= 50:
        return 2.50
    elif raw_score >= 45:
        return 2.00
    elif raw_score >= 40:
        return 1.50
    else:
        return 0.00

def main():
    num_units = int(input("Enter the number of units: "))
    
    total_grade_points = 0
    total_credits = 0
    
    for i in range(1, num_units + 1):
        raw_score = float(input("Enter the raw score for unit {}: ".format(i)))
        credit_hours = float(input("Enter the credit hours for unit {}: ".format(i)))
        
        grade_point = calculate_grade_point(raw_score)
        credits = credit_hours
        
        total_grade_points += grade_point * credits
        total_credits += credits
    
    overall_gpa = total_grade_points / total_credits
    
    print("Overall GPA:", overall_gpa)
    
    if overall_gpa >= 1.0:
        print("Good standing: Yes")
    else:
        print("Good standing: No")

if __name__ == "__main__":
    main()
