import os
import pandas as pd
from pathlib import Path
def clear(): os.system("cls" if os.name == "nt" else "clear")
clear()
pet_name = input("1.اسم پت شما چیست؟\n ")
clear()
while True:
    pet_species = input(f"2. گونه {pet_name} چیه؟\n""1. سگ\n""2. گربه\n""انتخاب شما: ")
    if pet_species in ["1", "2"]: break
pet_species = "dog" if pet_species == "1" else "cat"

clear()
while True:
    adult = input(f"3. آیا {pet_name} بالغ است؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if adult in ["1", "2"]: break

clear()
age_years = 0 if adult == "2" else int(input(f"سن {pet_name} چند سال است؟\n "))

clear()
age_months = int(input(f"سن {pet_name} چند ماه است؟\n "))

clear()
age_weeks = 0 if adult == "1" else int(input(f"سن {pet_name} چند هفته است؟\n "))

pet_age_weeks = 52.2 * age_years + 4.2 * age_months + age_weeks

clear()
if pet_species == "cat":
    while True:
        pet_breed = input(f"4. نژاد {pet_name} چیه؟\n""1. Domestic (DSH, DLH)\n""2. Exotic (Scottish, Persian, British و مشابه)\n""انتخاب شما: ")
        if pet_breed in ["1", "2"]: break
    pet_breed = "domestic" if pet_breed == "1" else "exotic"
else:
    while True:
        pet_breed = input(f"4. نژاد {pet_name} چیه؟\n""1. Toy\n""2. Small\n""3. Medium\n""4. Large\n""5. Giant\n""انتخاب شما: ")
        if pet_breed in ["1", "2", "3", "4", "5"]: break
    pet_breed = ["toy", "small", "medium", "large", "giant"][int(pet_breed) - 1]

if pet_species == "dog":
    if pet_breed == "toy":
        adult_weight_kg = 3.5
    elif pet_breed == "small":
        adult_weight_kg = 5.0
    elif pet_breed == "medium":
        adult_weight_kg = 15.0
    elif pet_breed == "large":
        adult_weight_kg = 25.0
    else:
        adult_weight_kg = 30.0
else:
    adult_weight_kg = 4.0

clear()
while True:
    sex = input(f"5. جنسیت {pet_name}؟\n""1. نر\n""2. ماده\n""انتخاب شما: ")
    if sex in ["1", "2"]: break
is_pregnant = False
is_lactating = False
is_growing = False
reproductive_status = None
lactation_weeks = None
litter_size = None
pregnancy_weeks = None
neutered = None

if pet_age_weeks < 17.0 :
    is_growing = True
    clear()

while True:
    neutered = input(f"6. آیا {pet_name} عقیم است؟\n""1. عقیم\n""2. غیرعقیم\n""انتخاب شما: ")
    if neutered in ["1", "2"]: break

if adult == "1" and sex == "2" and (is_growing == False) and neutered == "2" :
    while True:
        reproductive_status = input("5.1 وضعیت تولیدمثل؟\n""1. غیر آبستن و غیر شیرده\n""2. آبستن\n""3. شیرده\n""انتخاب شما: ")
        if reproductive_status in ["1", "2", "3"]: break

    if reproductive_status == "2":
        is_pregnant = True
        pregnancy_weeks = int(input("5.1.1 هفته آبستنی: "))

    elif reproductive_status == "3":
        is_lactating = True
        lactation_weeks = int(input("5.1.2 هفته شیردهی: "))
        litter_size = int(input("5.1.3 تعداد توله: "))


if is_pregnant:
    status = "bardar"
elif is_lactating:
    status = "shirde"
elif is_growing:
    status = "growing"
else:
    status = "adult"

clear()
weight = float(input(f"7.1 وزن فعلی {pet_name} (کیلوگرم):\n "))

clear()
while True:
    weight_date = input("7.2 تاریخ آخرین وزن‌کشی؟\n""1. کمتر از ۱ هفته\n""2. ۱ تا ۴ هفته\n""3. بیشتر از ۱ ماه\n""انتخاب شما: ")
    if weight_date in ["1", "2", "3"]: break

clear()
text1 = """1 - استخوان‌ها از دور دیده می‌شن، چربی نداره، عضله خیلی کم.
2 - دنده‌ها و استخوان لگن مشخص، چربی نداره، عضله کمی از بین رفته.
3 - دنده‌ها قابل لمس و کمی دیده می‌شن، چربی نداره، کمر و لگن برجسته.
4 - دنده‌ها به‌راحتی لمس می‌شن با کمی چربی، کمر قابل تشخیص، شکم جمع‌شده.
5 - دنده‌ها قابل لمس بدون چربی اضافی، کمر و جمع‌شدگی شکم به‌وضوح دیده می‌شن.
6 - دنده‌ها با اندکی چربی لمس می‌شن، کمر قابل دید ولی نه خیلی واضح.
7 - دنده‌ها سخت لمس می‌شن، چربی زیاد روی کمر و دم، کمر تقریباً محو.
8 - چربی زیاد، دنده‌ها فقط با فشار زیاد لمس می‌شن، شکم و کمر دیده نمی‌شن.
9 - چربی خیلی زیاد روی قفسه سینه و کمر، شکم افتاده، گردن و اندام‌ها هم چاق."""

text2 = """1 - دنده‌ها و ستون فقرات کاملاً دیده می‌شن، چربی صفر، شکم خیلی جمع شده.
2 - دنده‌ها واضح، عضله کم، چربی نداره، شکم کاملاً جمع.
3 - دنده‌ها به‌آسانی لمس می‌شن با چربی خیلی کم، کمر مشخص، شکم کمی جمع.
4 - دنده‌ها راحت لمس می‌شن با چربی کم، کمر مشخص، پد چربی شکمی وجود نداره.
5 - بدن متناسب، دنده‌ها با کمی چربی لمس می‌شن، چربی شکمی خیلی کم یا صفر.
6 - دنده‌ها کمی سخت‌تر لمس می‌شن، چربی کم روی شکم، کمر خیلی واضح نیست.
7 - دنده‌ها سخت لمس می‌شن، چربی متوسط، شکم گرد، پد چربی شکمی وجود داره.
8 - دنده‌ها تقریباً لمس نمی‌شن، فرورفتگی شکم وجود نداره، چربی زیاد مخصوصاً شکم.
9 - چربی خیلی زیاد کمر، شکم، صورت و پاها؛ شکم آویزان؛ دنده‌ها اصلاً لمس نمی‌شن."""

clear()
bcs_text = text1 if pet_species == "dog" else text2
print(f"7.4 اسکور بدنی (BCS) {pet_name}:\n\n{bcs_text}")
while True:
    bcs = input("\nاسکور BCS (1 تا 9): ")
    if bcs in [str(i) for i in range(1, 10)]: break
bcs = int(bcs)

clear()
while True:
    activity = input(f"8. میزان فعالیت {pet_name} (۱ تا ۱۰):\n""۱. کاملاً بی‌تحرک / فقط داخل خانه بدون حرکت\n""۵. فعالیت متوسط روزانه\n""۱۰. فعالیت بسیار شدید / کار سنگین / ورزش حرفه‌ای\n""انتخاب شما: ")
    if activity.isdigit() and 1 <= int(activity) <= 10: break
activity = int(activity)

clear()
while True:
    food_allergy = input(f"15. آیا {pet_name} حساسیت غذایی شناخته‌شده دارد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if food_allergy in ["1", "2"]: break

allergens = []
if food_allergy == "1":
    food_file = Path.home() / "Desktop" / "Panje" / "Infos" / "Food Central" / "Home foods.xlsx"
    foods = pd.read_excel(food_file)

    clear()
    print("15.1 دسته ماده حساسیت‌زا را انتخاب کنید (چند انتخابی):\n")
    categories = foods["category"].dropna().unique()
    for i, category in enumerate(categories, 1): print(f"{i}. {category}")
    print(f"{len(categories) + 1}. سایر")

    while True:
        category_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if category_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in category_choices):
            break


    for choice in category_choices:
        if int(choice) == len(categories) + 1:
            clear()
            allergens.append(input("15.1 سایر مواد حساسیت‌زا: "))
            continue

        selected_category = categories[int(choice) - 1]
        category_foods = foods[foods["category"] == selected_category]["foods"].dropna().tolist()

        clear()
        print(f"15.1 مواد حساسیت‌زا از دسته «{selected_category}» (چند انتخابی):\n")
        for i, food in enumerate(category_foods, 1): print(f"{i}. {food}")
        print(f"{len(category_foods) + 1}. سایر")

        selections = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        allergens += [category_foods[int(i) - 1] for i in selections if
                      i.isdigit() and 1 <= int(i) <= len(category_foods)]

        if str(len(category_foods) + 1) in selections:
            clear()
            allergens.append(input("15.1 سایر مواد حساسیت‌زا: "))
clear()


# ----- Base path for all food Excel files -----
food_central = Path.home() / "Desktop" / "Panje" / "Infos" / "Food Central"


selected_foods = []
current_diet = []

meals_per_day = 0
wet_meals_per_day = 0
home_meals_per_day = 0
treat_frequency = 0
supplement_types = 0

# We'll build two parallel flat lists:
selected_foods = []   # food names (strings)
current_diet = []     # grams per day (floats)



# ---------- Dry Food ----------
while True:
    dry_food = input(f"19. آیا {pet_name} غذای خشک مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if dry_food in ["1", "2"]: break

if dry_food == "1":
    dry_file = food_central / "Dry foods.xlsx"
    foods = pd.read_excel(dry_file)
    categories = foods["category"].dropna().unique()
    dry_details = []

    clear()
    print("19.1 دسته ماده غذایی خشک را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("19.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام ماده خشک از دسته «{cat}»: ")
            print(f"\n--- برای {food_name} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. پیمانه ۲۵۰ سی‌سی\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "cup_250ml"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            dry_details.append((food_name, meals, unit, amount))
            clear()
            continue

        clear()
        print(f"19.1 مواد خشک از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        selected_foods_list = []
        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام ماده خشک از دسته «{cat}»: ")
                selected_foods_list.append(other_food)
                continue
            selected_foods_list.append(cat_foods[int(choice) - 1])

        for food in selected_foods_list:
            print(f"\n--- برای {food} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. پیمانه ۲۵۰ سی‌سی\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "cup_250ml"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            dry_details.append((food, meals, unit, amount))
            clear()

    # Additional questions
    while True:
        eating_speed = input("19.4 سرعت غذا خوردن؟\n""1. زیر ۱ دقیقه می‌بلعد\n""2. ۱ تا ۵ دقیقه\n""3. ۵ تا ۱۵ دقیقه\n""4. بالای ۱۵ دقیقه یا در طول روز کم‌کم\n""انتخاب شما: ")
        if eating_speed in ["1", "2", "3", "4"]:
            break
    # clear()
    # while True:
    #     bowl_type = input("19.5 جنس ظرف غذا؟\n""1. پلاستیکی\n""2. استیل\n""3. سرامیکی\n""4. ملامین\n""5. پایه بلند\n""6. پازلی / ضدبلع\n""انتخاب شما: ")
    #     if bowl_type in ["1", "2", "3", "4", "5", "6"]:
    #         break
    clear()
    print("19.6 برنامه زمانی وعده‌ها (چند گزینه‌ای):\n"
          "1. ساعات منظم و بی‌ربط به غذای خانواده\n"
          "2. نامنظم و بی‌ربط به غذای خانواده\n"
          "3. قبل از غذای خانواده\n"
          "4. بعد از غذای خانواده\n"
          "5. همزمان با غذای خانواده")
    feeding_schedule = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    clear()

# ---------- Wet Food ----------
while True:
    wet_food = input(f"20. آیا {pet_name} غذای تر (کنسروی و مشابه) مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if wet_food in ["1", "2"]:
        break

if wet_food == "1":
    wet_file = food_central / "Wet foods.xlsx"
    foods = pd.read_excel(wet_file)
    categories = foods["category"].dropna().unique()
    wet_details = []

    clear()
    print("20.1 دسته غذای تر را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("20.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام غذای تر از دسته «{cat}»: ")
            print(f"\n--- برای {food_name} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. کنسرو\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "can"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            wet_details.append((food_name, meals, unit, amount))
            clear()
            continue

        clear()
        print(f"20.1 مواد تر از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        selected_foods_list = []
        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام غذای تر از دسته «{cat}»: ")
                selected_foods_list.append(other_food)
                continue
            selected_foods_list.append(cat_foods[int(choice) - 1])

        for food in selected_foods_list:
            print(f"\n--- برای {food} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. کنسرو\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "can"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            wet_details.append((food, meals, unit, amount))
            clear()

    # Additional questions
    while True:
        wet_eating_speed = input("20.4 سرعت غذا خوردن؟\n""1. زیر ۱ دقیقه می‌بلعد\n""2. ۱ تا ۵ دقیقه\n""3. ۵ تا ۱۵ دقیقه\n""4. بالای ۱۵ دقیقه یا در طول روز کم‌کم\n""انتخاب شما: ")
        if wet_eating_speed in ["1", "2", "3", "4"]:
            break
    # clear()
    # while True:
    #     wet_bowl_type = input("20.5 جنس ظرف غذا؟\n""1. پلاستیکی\n""2. استیل\n""3. سرامیکی\n""4. ملامین\n""5. پایه بلند\n""6. پازلی / ضدبلع\n""انتخاب شما: ")
    #     if wet_bowl_type in ["1", "2", "3", "4", "5", "6"]:
    #         break
    clear()
    print("20.6 برنامه زمانی وعده‌ها (چند گزینه‌ای):\n"
          "1. ساعات منظم و بی‌ربط به غذای خانواده\n"
          "2. نامنظم و بی‌ربط به غذای خانواده\n"
          "3. قبل از غذای خانواده\n"
          "4. بعد از غذای خانواده\n"
          "5. همزمان با غذای خانواده")
    wet_feeding_schedule = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    clear()

# ---------- Treats ----------
while True:
    treats = input(f"21. آیا {pet_name} تشویقی می‌خورد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if treats in ["1", "2"]:
        break

if treats == "1":
    treats_file = food_central / "Treats.xlsx"
    foods = pd.read_excel(treats_file)
    categories = foods["category"].dropna().unique()
    treat_details = []

    clear()
    print("21.1 دسته تشویقی را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("21.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام تشویقی از دسته «{cat}»: ")
            print(f"\n--- برای {food_name} ---")
            while True:
                freq = input("تعداد دفعات در روز: ")
                if freq.isdigit() and int(freq) >= 1:
                    break
            freq = int(freq)
            while True:
                unit = input("واحد مقدار هر تشویقی:\n""1. گرم\n""2. عدد\n""انتخاب شما: ")
                if unit in ["1", "2"]:
                    break
            unit = "gram" if unit == "1" else "piece"
            amount = float(input("مقدار هر تشویقی: "))
            treat_details.append((food_name, freq, unit, amount))
            clear()
            continue

        clear()
        print(f"21.1 تشویقی‌های دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        selected_foods_list = []
        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام تشویقی از دسته «{cat}»: ")
                selected_foods_list.append(other_food)
                continue
            selected_foods_list.append(cat_foods[int(choice) - 1])

        for food in selected_foods_list:
            print(f"\n--- برای {food} ---")
            while True:
                freq = input("تعداد دفعات در روز: ")
                if freq.isdigit() and int(freq) >= 1:
                    break
            freq = int(freq)
            while True:
                unit = input("واحد مقدار هر تشویقی:\n""1. گرم\n""2. عدد\n""انتخاب شما: ")
                if unit in ["1", "2"]:
                    break
            unit = "gram" if unit == "1" else "piece"
            amount = float(input("مقدار هر تشویقی: "))
            treat_details.append((food, freq, unit, amount))
            clear()

    # Additional questions
    print("21.3 زمان‌های دادن (چند انتخابی):\n"
          "1. صبح\n""2. ظهر\n""3. عصر\n""4. شب\n""5. هنگام آموزش\n""6. سایر")
    treat_times = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    if "6" in treat_times:
        clear()
        treat_time_other = input("21.3.6 سایر زمان: ")
    clear()

# ---------- Home Food ----------
while True:
    home_food = input(f"22. آیا {pet_name} غذای خانگی مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if home_food in ["1", "2"]:
        break

if home_food == "1":
    homefood_file = food_central / "Home foods.xlsx"
    foods = pd.read_excel(homefood_file)
    categories = foods["category"].dropna().unique()
    home_details = []

    clear()
    print("22.1 دسته غذای خانگی را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("22.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام غذای خانگی از دسته «{cat}»: ")
            print(f"\n--- برای {food_name} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. پیمانه ۲۵۰ سی‌سی\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "cup_250ml"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            home_details.append((food_name, meals, unit, amount))
            clear()
            continue

        clear()
        print(f"22.1 مواد غذایی از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        selected_foods_list = []
        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام غذای خانگی از دسته «{cat}»: ")
                selected_foods_list.append(other_food)
                continue
            selected_foods_list.append(cat_foods[int(choice) - 1])

        for food in selected_foods_list:
            print(f"\n--- برای {food} ---")
            while True:
                meals = input("تعداد وعده در روز (۱ تا ۶ یا بیشتر): ")
                if meals.isdigit() and int(meals) >= 1:
                    break
            meals = int(meals)
            while True:
                unit = input("واحد مقدار هر وعده:\n""1. گرم\n""2. قاشق غذاخوری\n""3. پیمانه ۲۵۰ سی‌سی\n""انتخاب شما: ")
                if unit in ["1", "2", "3"]:
                    break
            unit = ["gram", "tablespoon", "cup_250ml"][int(unit) - 1]
            amount = float(input("مقدار هر وعده: "))
            home_details.append((food, meals, unit, amount))
            clear()

    # Additional questions
    # while True:
    #     preparation = input("22.2 روش آماده‌سازی؟\n""1. کاملاً پخته\n""2. نیم‌پز\n""3. خام\n""4. ترکیبی\n""انتخاب شما: ")
    #     if preparation in ["1", "2", "3", "4"]:
    #         break
    clear()
    while True:
        home_eating_speed = input("22.4 سرعت غذا خوردن؟\n""1. زیر ۱ دقیقه می‌بلعد\n""2. ۱ تا ۵ دقیقه\n""3. ۵ تا ۱۵ دقیقه\n""4. بالای ۱۵ دقیقه یا در طول روز کم‌کم\n""انتخاب شما: ")
        if home_eating_speed in ["1", "2", "3", "4"]:
            break
    # clear()
    # while True:
    #     home_bowl_type = input("22.4 جنس ظرف غذا؟\n""1. پلاستیکی\n""2. استیل\n""3. سرامیکی\n""4. ملامین\n""5. پایه بلند\n""6. پازلی / ضدبلع\n""انتخاب شما: ")
    #     if home_bowl_type in ["1", "2", "3", "4", "5", "6"]:
    #         break
    clear()
    print("22.4 برنامه زمانی وعده‌ها (چند انتخابی):\n"
          "1. ساعات منظم و بی‌ربط به غذای خانواده\n"
          "2. نامنظم و بی‌ربط به غذای خانواده\n"
          "3. قبل از غذای خانواده\n"
          "4. بعد از غذای خانواده\n"
          "5. همزمان با غذای خانواده")
    home_feeding_schedule = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    clear()

# ---------- Supplements ----------
while True:
    supplements = input(f"23. آیا {pet_name} مکمل مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if supplements in ["1", "2"]:
        break

if supplements == "1":
    supp_file = food_central / "Supplements.xlsx"
    supp_df = pd.read_excel(supp_file)
    supp_list = supp_df["مکمل"].dropna().unique().tolist()
    clear()
    print("23.1 نوع مکمل (چند انتخابی):\n")
    for i, supp in enumerate(supp_list, 1): print(f"{i}. {supp}")
    print(f"{len(supp_list) + 1}. سایر")
    supp_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    supplement_types = []
    for ch in supp_choices:
        if ch.isdigit():
            idx = int(ch)
            if 1 <= idx <= len(supp_list):
                supplement_types.append(supp_list[idx - 1])
            elif idx == len(supp_list) + 1:
                clear()
                other = input("23.1 سایر مکمل: ")
                supplement_types.append(other)

    clear()
    while True:
        supplement_method = input("23.2 نحوه دادن؟\n""1. مخلوط با غذا\n""2. جداگانه\n""3. هر دو\n""انتخاب شما: ")
        if supplement_method in ["1", "2", "3"]:
            break
    clear()

# ================================================================
# Now build the two parallel lists: selected_foods and current_diet
# ================================================================

def to_grams(amount, unit):
    if unit == 'gram':
        return amount
    elif unit == 'tablespoon':
        return amount * 15
    elif unit == 'cup_250ml':
        return amount * 100
    elif unit == 'can':
        return amount * 400
    elif unit == 'piece':
        return amount * 5   # treat weight estimate
    else:
        return amount

# ---------- Dry food ----------
if dry_food == "1":
    for food, meals, unit, amount in dry_details:
        total_grams = to_grams(amount, unit) * meals
        selected_foods.append(f"Dry Food - {food}")
        current_diet.append(round(total_grams, 1))

# ---------- Wet food ----------
if wet_food == "1":
    for food, meals, unit, amount in wet_details:
        total_grams = to_grams(amount, unit) * meals
        selected_foods.append(f"Wet Food - {food}")
        current_diet.append(round(total_grams, 1))

# ---------- Treats ----------
if treats == "1":
    for food, freq, unit, amount in treat_details:
        total_grams = to_grams(amount, unit) * freq
        selected_foods.append(f"Treats - {food}")
        current_diet.append(round(total_grams, 1))

# ---------- Home food ----------
if home_food == "1":
    for food, meals, unit, amount in home_details:
        total_grams = to_grams(amount, unit) * meals
        selected_foods.append(f"Home Food - {food}")
        current_diet.append(round(total_grams, 1))

# ---------- Supplements ----------
if supplements == "1":
    for supp in supplement_types:
        selected_foods.append(f"Supplement - {supp}")
        current_diet.append(0)


# ----- Collect the initial diet -----
# selected_foods, current_diet = collect_diet(pet_name, food_central, clear)


# Optional: print the lists to verify
print("\n📋 Current diet (flat lists):")
print("selected_foods:", selected_foods)
print("current_diet  :", current_diet)

# ================================================================
# Compute total daily nutrient intake from selected_foods & current_diet
# ================================================================

def compute_total_nutrients(selected_foods, current_diet, food_central):
    """
    Returns a dict of total daily nutrient intake (same units as per 100g in the Excel files).
    """
    # Mapping from food prefix to Excel filename
    source_map = {
        "Dry Food": "Dry foods.xlsx",
        "Wet Food": "Wet foods.xlsx",
        "Treats": "Treats.xlsx",
        "Home Food": "Home foods.xlsx",
        "Supplement": "Supplements.xlsx"
    }

    # Load each Excel file once into a dict of DataFrames
    dfs = {}
    for prefix, fname in source_map.items():
        file_path = food_central / fname
        if file_path.exists():
            df = pd.read_excel(file_path)
            # Ensure we have the required columns
            if "foods" not in df.columns:
                print(f"Warning: {fname} does not have a 'foods' column. Skipping.")
                continue
            dfs[prefix] = df
        else:
            print(f"Warning: {fname} not found at {file_path}")

    total_nutrients = {}

    for food_name, grams in zip(selected_foods, current_diet):
        # Skip foods with 0 grams (e.g., supplements with no weight)
        if grams == 0:
            continue

        # Identify prefix
        matched_prefix = None
        actual_name = None
        for prefix in source_map:
            if food_name.startswith(prefix + " - "):
                matched_prefix = prefix
                actual_name = food_name[len(prefix) + 3:]  # remove " - "
                break

        if matched_prefix is None:
            print(f"Warning: Unknown food prefix in '{food_name}'")
            continue

        df = dfs.get(matched_prefix)
        if df is None:
            continue

        # Find the row where "foods" == actual_name
        row = df[df["foods"] == actual_name]
        if row.empty:
            print(f"Warning: '{actual_name}' not found in {matched_prefix} file.")
            continue

        # Get all numeric columns except 'category' and 'foods' and 'kcal/100gr'
        nutrient_cols = [col for col in df.columns if col not in ["category", "foods", "kcal/100gr"]]

        for col in nutrient_cols:
            val = row.iloc[0][col]
            # Try to convert to float; skip if not numeric
            try:
                val = float(val)
            except (ValueError, TypeError):
                continue
            # Add contribution: val is per 100g, so multiply by (grams / 100)
            total_nutrients[col] = total_nutrients.get(col, 0.0) + val * (grams / 100.0)

    return total_nutrients

# ---- Use the function ----
food_central = Path.home() / "Desktop" / "Panje" / "Infos" / "Food Central"
total_intake = compute_total_nutrients(selected_foods, current_diet, food_central)

# Display the results (optional)
print("\n📊 Total Daily Nutrient Intake from current diet:")
for nutrient, amount in total_intake.items():
    print(f"  {nutrient}: {amount:.2f}")

if pet_species == "dog":
    if status == "bardar":
        if pregnancy_weeks >= 12:
            ME = 130 * (weight ** 0.75) + 26 * weight
        else:
            ME = 130 * (weight ** 0.75)
    elif status == "shirde":
        if litter_size <= 4:
            ME = 145 * (weight ** 0.75) + weight * 24 * litter_size * lactation_weeks * 0.05
        else:
            ME = 145 * (weight ** 0.75) + weight * (48 + 12 * litter_size) * lactation_weeks * 0.05
    elif status == "growing":
        if weight < adult_weight_kg:
            ME = 130 * 3.2 * (weight ** 0.75) * (2.7828 ** (-0.87 * weight / adult_weight_kg) - 0.1)
        else:
            ME = 130 * (weight ** 0.75)
    else:
        if pet_breed == "toy":
            z = 1.8
        elif pet_breed == "small":
            z = 1.6
        elif pet_breed == "medium":
            z = 1.4
        else:
            z = 1.2
        if bcs in [1, 2, 3]:
            z = 1.8 - (bcs - 1) * 0.2
        elif bcs in [4, 5, 6]:
            z = 0.95 + 0.25 * activity
        elif bcs >= 7:
            z = 1.2
        if neutered == "1":
            z -= 0.2
        ME = z * 70 * (weight ** 0.75)

else:
    if status == "bardar":
        ME = 140 * (weight ** 0.67)
    elif status == "shirde":
        if lactation_weeks <= 2:
            ws = 0.90
        elif lactation_weeks == 3:
            ws = 1.20
        elif lactation_weeks == 4:
            ws = 1.20
        elif lactation_weeks == 5:
            ws = 1.10
        elif lactation_weeks == 6:
            ws = 1.00
        else:
            ws = 0.80
        if litter_size <= 2:
            ME = 100 * (weight ** 0.67) + 18 * weight * ws
        elif litter_size <= 4:
            ME = 100 * (weight ** 0.67) + 60 * weight * ws
        else:
            ME = 100 * (weight ** 0.67) + 70 * weight * ws
    elif status == "growing":
        if weight < adult_weight_kg:
            ME = 100 * 6.7 * (weight ** 0.67) * (2.7828 ** (-0.189 * weight / adult_weight_kg) - 0.66)
        else:
            ME = 84 * (weight ** 0.75)
    else:
        if pet_breed == "exotic":
            z = 1.5 if age_years < 7 else 1.4 if age_years == 7 else 1.3 if age_years == 8 else 1.2 if age_years == 9 else 1.1
        else:
            z = 1.8 if bcs == 1 else 1.6 if bcs == 2 else 1.4 if bcs == 3 else 1.0 if bcs >= 7 else (1.5 if age_years < 7 else 1.4 if age_years == 7 else 1.3 if age_years == 8 else 1.2 if age_years == 9 else 1.1)
        if bcs in [1, 2, 3]:
            z = 1.8 - (bcs - 1) * 0.2
        if neutered == "1":
            z -= 0.2
        ME = z * 70 * (weight ** 0.75)

clear()


desktop = Path.home() / "Desktop"
req_base = desktop / "Panje" / "Infos" / "Requirements"

if pet_species == "dog":
    if is_pregnant or is_lactating:
        excel_file = "Late_Gest,Peak_Lac,Dog.xlsx"
    elif adult == "2":
        if pet_age_weeks < 14:
            excel_file = "Puppies4-14.xlsx"
        else:
            excel_file = "Puppies14-more.xlsx"
    else:
        excel_file = "Adult_Dogs.xlsx"

elif pet_species == "cat":
    if is_pregnant:
        excel_file = "Gestation,Cat.xlsx"
    elif is_lactating:
        excel_file = "Peak_Lac,Cat .xlsx"
    elif adult == "2":
        excel_file = "Kitten.xlsx"
    else:
        excel_file = "Adult_Cats.xlsx"

full_excel_path = req_base / excel_file
if not full_excel_path.exists():
    print(f"❌ ERROR: File '{excel_file}' not found at:\n{full_excel_path}")
    print("   Make sure the file exists in the correct folder.")
    exit()
else:
    df = pd.read_excel(full_excel_path, sheet_name="Sheet1", header=0)
    df.columns = ["Nutrient", "MR", "RA", "SUL"]


def safe_convert(val):
    if isinstance(val, (int, float)):
        return float(val)
    if isinstance(val, str):
        val = val.strip()
        if val.startswith('='):
            try:
                return eval(val[1:], {"__builtins__": None}, {})
            except:
                return float('nan')
        else:
            try:
                return float(val)
            except:
                return float('nan')
    return float('nan')


for col in ["MR", "RA", "SUL"]:
    df[col] = df[col].apply(safe_convert)

df = df.dropna(subset=["MR", "RA", "SUL"], how="all")

df.set_index("Nutrient", inplace=True)

scale_factor = ME / 1000.0

daily_requirements = df[["MR", "RA", "SUL"]].copy()
daily_requirements["MR"] = daily_requirements["MR"] * scale_factor
daily_requirements["RA"] = daily_requirements["RA"] * scale_factor
daily_requirements["SUL"] = daily_requirements["SUL"] * scale_factor

print("\n" + "=" * 60)
print(f"📊 Daily nutrient requirements for {pet_name} ({pet_species})")
print(f"   Total Daily Energy = {ME:.1f} kcal ME")
print(f"   Scaling factor (ME/1000) = {scale_factor:.4f}")
print("=" * 60)

print(daily_requirements.to_string())
print("\n")   # extra blank line after the table

from fpdf import FPDF
from datetime import datetime
import os
import pandas as pd

import arabic_reshaper
from bidi.algorithm import get_display
import warnings
warnings.filterwarnings("ignore", category=DeprecationWarning)  # optional
import unicodedata


def generate_pet_report(
        pet_name, pet_species, pet_breed, sex, weight, bcs, activity,
        neutered, status, ME, excel_file, daily_requirements_df,
        selected_foods=None, current_diet=None, total_intake=None, allergens=None
):
    # ----- Helpers for Persian text -----
    def is_persian(text):
        if not text:
            return False
        for ch in str(text):
            if 0x0600 <= ord(ch) <= 0x06FF or 0x0750 <= ord(ch) <= 0x077F:
                return True
        return False

    def reshape_text(text):
        text = str(text)
        if is_persian(text):
            reshaped = arabic_reshaper.reshape(text)
            return get_display(reshaped)
        return text

    pdf = FPDF(orientation='P', unit='mm', format='A4')
    pdf.add_page()
    pdf.set_margins(left=15, top=15, right=15)

    # ----- Font: Vazirmatn -----
    font_dir = Path.home() / "Desktop" / "Panje" / "Infos" / "Requirements"
    regular_font = font_dir / "Vazirmatn-Regular.ttf"
    bold_font = font_dir / "Vazirmatn-Bold.ttf"

    if not regular_font.exists():
        print(f"\n❌ ERROR: Vazirmatn-Regular.ttf not found at:\n{regular_font}")
        exit(1)

    pdf.add_font('Vazirmatn', '', str(regular_font))
    if bold_font.exists():
        pdf.add_font('Vazirmatn', 'B', str(bold_font))
    else:
        pdf.add_font('Vazirmatn', 'B', str(regular_font))

    pdf.add_font('Vazirmatn', 'I', str(regular_font))
    pdf.add_font('Vazirmatn', 'BI', str(regular_font))

    pdf.set_font('Vazirmatn', '', 12)
    font_name = 'Vazirmatn'

    def set_font(style='', size=12):
        pdf.set_font(font_name, style, size)

    def set_text_color(r, g, b):
        pdf.set_text_color(r, g, b)

    # ---- Title ----
    pdf.set_font_size(18)
    set_font('B', 18)
    pdf.cell(0, 10, reshape_text(f"{pet_name}'s Nutrition Report"), ln=True, align='C')
    pdf.set_font_size(12)
    set_font('', 12)
    pdf.cell(0, 8, reshape_text(f"Generated on: {datetime.now().strftime('%Y-%m-%d %H:%M')}"), ln=True, align='C')
    pdf.ln(5)

    # ---- 1. Pet Information ----
    pdf.set_font_size(14)
    set_font('B', 14)
    pdf.cell(0, 8, "Pet Information", ln=True)
    set_font('', 12)
    pdf.set_font_size(12)

    species_label = "Dog" if pet_species == "dog" else "Cat"
    sex_label = "Male" if sex == "1" else "Female"
    neutered_label = "Yes" if neutered == "1" else "No"
    status_label = {
        "adult": "Adult (Maintenance)",
        "growing": "Growing / Puppy / Kitten",
        "bardar": "Pregnant",
        "shirde": "Lactating"
    }.get(status, status)

    info = [
        ("Name", pet_name),
        ("Species", species_label),
        ("Breed", pet_breed.capitalize()),
        ("Sex", sex_label),
        ("Neutered", neutered_label),
        ("Life Stage", status_label),
        ("Current Weight", f"{weight:.2f} kg"),
        ("Body Condition Score (BCS)", f"{bcs} / 9"),
        ("Activity Level", f"{activity} / 10"),
        ("Daily Energy (ME)", f"{ME:.1f} kcal / day"),
        ("Requirements Source", os.path.basename(excel_file))
    ]

    label_width = 65
    value_width = 70
    col_gap = 20
    row_height_info = 8
    x_start = pdf.get_x()
    for i, (key, value) in enumerate(info):
        if i % 2 == 0:
            pdf.set_x(x_start)
        else:
            pdf.set_x(x_start + label_width + col_gap)
        set_font('B', 12)
        pdf.cell(label_width, row_height_info, reshape_text(f"{key}:"), border=0)
        set_font('', 12)
        pdf.cell(value_width, row_height_info, reshape_text(f" {value}"), border=0, ln=(i % 2 == 1))

    # ---- ADD EXTRA SPACE BEFORE "Current Diet" ----
    pdf.ln(10)  # Increased from 5 to 10 mm for more separation

    # ---- 2. Current Diet ----
    if selected_foods and current_diet:
        pdf.set_font_size(14)
        set_font('B', 14)
        pdf.cell(0, 8, "Current Diet (grams per day)", ln=True)
        set_font('', 11)
        pdf.set_font_size(11)

        for food, grams in zip(selected_foods, current_diet):
            if grams > 0:
                pdf.cell(10, 7, "•", border=0)
                pdf.cell(100, 7, reshape_text(f"{food}:"), border=0)
                pdf.cell(30, 7, reshape_text(f"{grams:.1f} g"), border=0, ln=1)
        pdf.ln(2)

    # ---- 3. Allergens ----
    if allergens:
        pdf.set_font_size(14)
        set_font('B', 14)
        pdf.cell(0, 8, "Known Allergens", ln=True)
        set_font('', 11)
        pdf.set_font_size(11)
        pdf.multi_cell(0, 7, reshape_text(", ".join(allergens)))
        pdf.ln(2)

    # ---- 4. Nutrient Table (5 columns) ----
    pdf.set_font_size(14)
    set_font('B', 14)
    pdf.cell(0, 8, "Nutrient Comparison", ln=True)
    set_font('', 10)
    pdf.set_font_size(10)

    df_table = daily_requirements_df.copy()
    df_table = df_table[df_table["RA"] > 0].dropna(how='all')
    df_display = df_table.copy()
    for col in ["MR", "RA", "SUL"]:
        df_display[col] = df_display[col].round(2)

    table_rows = []
    header = ["Nutrient", "MR (Min)", "RA (Rec)", "SUL (Max)", "Current Intake"]
    table_rows.append(header)

    for nutrient, row in df_display.iterrows():
        mr = row['MR'] if pd.notna(row['MR']) else None
        ra = row['RA'] if pd.notna(row['RA']) else None
        sul = row['SUL'] if pd.notna(row['SUL']) else None

        current_val = total_intake.get(nutrient) if total_intake else None
        current_str = f"{current_val:.2f}" if current_val is not None else "N/A"

        if current_val is None:
            color = None
        elif mr is not None and current_val < mr:
            color = (255, 0, 0)
        elif sul is not None and current_val > sul:
            color = (255, 0, 0)
        else:
            color = (0, 150, 0)

        table_rows.append({
            'nutrient': nutrient,
            'mr': f"{mr:.2f}" if mr is not None else "-",
            'ra': f"{ra:.2f}" if ra is not None else "-",
            'sul': f"{sul:.2f}" if sul is not None else "-",
            'current': current_str,
            'current_val': current_val,
            'color': color
        })

    # ---------- WIDER NUTRIENT COLUMN ----------
    col_widths = [85, 22, 22, 22, 29]  # Total = 180 mm (fits within margins)
    row_height = 7
    page_height = 257
    x_start = pdf.get_x()

    def draw_table_row(values, is_header=False, color=None):
        pdf.set_x(x_start)
        for i, text in enumerate(values):
            if is_header:
                set_font('B', 10)
                pdf.set_text_color(0, 0, 0)
                pdf.cell(col_widths[i], row_height, reshape_text(str(text)), border=1, ln=0,
                         align='L' if i == 0 else 'R')
            else:
                set_font('', 10)
                if color and i == 4:
                    pdf.set_text_color(color[0], color[1], color[2])
                else:
                    pdf.set_text_color(0, 0, 0)
                if i == 0:
                    pdf.cell(col_widths[i], row_height, reshape_text(str(text)), border=1, ln=0, align='R')
                else:
                    pdf.cell(col_widths[i], row_height, str(text), border=1, ln=0, align='R')
        pdf.ln(row_height)
        pdf.set_text_color(0, 0, 0)

    draw_table_row(header, is_header=True)

    for row in table_rows[1:]:
        row_vals = [row['nutrient'], row['mr'], row['ra'], row['sul'], row['current']]
        if pdf.get_y() + row_height > page_height:
            pdf.add_page()
            draw_table_row(header, is_header=True)
        draw_table_row(row_vals, color=row['color'])

    pdf.ln(2)

    # ---- 5. Insufficiencies and Excesses ----
    deficient = []
    excessive = []
    if total_intake:
        for nutrient, row in df_display.iterrows():
            mr = row['MR'] if pd.notna(row['MR']) else None
            sul = row['SUL'] if pd.notna(row['SUL']) else None
            cur = total_intake.get(nutrient)
            if cur is not None:
                if mr is not None and cur < mr:
                    deficient.append(nutrient)
                if sul is not None and cur > sul:
                    excessive.append(nutrient)

    pdf.set_font_size(12)
    if deficient:
        set_font('B', 12)
        pdf.set_text_color(255, 0, 0)
        pdf.cell(0, 8, "WARNING: Nutritional Insufficiencies (below MR):", ln=True)
        set_font('', 12)
        pdf.set_text_color(0, 0, 0)
        pdf.multi_cell(0, 7, reshape_text(", ".join(deficient)))
        pdf.ln(2)
    else:
        set_font('', 12)
        pdf.set_text_color(0, 100, 0)
        pdf.cell(0, 8, "OK: No nutritional insufficiencies.", ln=True)
        pdf.set_text_color(0, 0, 0)

    if excessive:
        set_font('B', 12)
        pdf.set_text_color(255, 0, 0)
        pdf.cell(0, 8, "WARNING: Excessive Nutrients (above SUL):", ln=True)
        set_font('', 12)
        pdf.set_text_color(0, 0, 0)
        pdf.multi_cell(0, 7, reshape_text(", ".join(excessive)))
        pdf.ln(2)
    else:
        set_font('', 12)
        pdf.set_text_color(0, 100, 0)
        pdf.cell(0, 8, "OK: No excessive nutrients.", ln=True)
        pdf.set_text_color(0, 0, 0)

    # ---- Save PDF ----
    desktop = Path.home() / "Desktop"
    pdf_base = desktop / "Panje" / "Pet Infos" / pet_name
    pdf_base.mkdir(parents=True, exist_ok=True)
    pdf_path = pdf_base / "requirements.pdf"
    pdf.output(pdf_path)
    print(f"\n✅ PDF report saved as: {pdf_path}")
    return pdf_path

report_file = generate_pet_report(
    pet_name=pet_name,
    pet_species=pet_species,
    pet_breed=pet_breed,
    sex=sex,
    weight=weight,
    bcs=bcs,
    activity=activity,
    neutered=neutered,
    status=status,
    ME=ME,
    excel_file=excel_file,
    daily_requirements_df=daily_requirements,
    selected_foods=selected_foods,
    current_diet=current_diet,
    total_intake=total_intake,
    allergens=allergens
)
print(f"📄 Report saved to:\n{report_file}")

clear()
print("\n" + "=" * 60)
print("📋 THE NUTRITION REPORT IS READY")
print("=" * 60)

while True:
    new_diet_choice = input("\nآیا می‌خواهید یک رژیم غذایی جدید با مقادیر اصلاح‌شده طراحی شود؟\n"
                            "1. بله، رژیم جدید طراحی شود\n"
                            "2. خیر، فقط مکمل‌های پیشنهادی را ببینم\n"
                            "انتخاب شما: ")
    if new_diet_choice in ["1", "2"]:
        break

if new_diet_choice == "2":
    # ---- Option 2: Suggest supplements for deficiencies ----
    clear()
    print("\n" + "=" * 60)
    print("💊 پیشنهاد مکمل‌ها برای کمبودهای تغذیه‌ای")
    print("=" * 60)

    # Collect deficient nutrients from the earlier calculation
    deficient = []
    if total_intake:
        for nutrient, row in daily_requirements.iterrows():
            mr = row['MR'] if pd.notna(row['MR']) else None
            cur = total_intake.get(nutrient)
            if cur is not None and mr is not None and cur < mr:
                deficient.append((nutrient, mr - cur))

    if deficient:
        print("\nموارد زیر کمتر از حداقل نیاز (MR) هستند:")
        for nutrient, deficit in deficient:
            print(f"  • {nutrient}: کمبود {deficit:.2f} واحد در روز")
        print("\n💡 پیشنهاد مکمل:")
        print("   - برای کمبود پروتئین: پودر پروتئین یا تخم‌مرغ")
        print("   - برای کمبود اسیدهای آمینه: مکمل‌های اسید آمینه یا غذاهای غنی از پروتئین")
        print("   - برای کمبود ویتامین‌ها و مواد معدنی: مکمل‌های مخصوص سگ/گربه")
        print("   - برای کمبود اسیدهای چرب: روغن ماهی یا روغن کتان")
        print("\n⚠️ همیشه قبل از دادن هر مکملی با دامپزشک مشورت کنید.")
    else:
        print("\n✅ همه مواد مغذی در حد نیاز هستند – نیازی به مکمل نیست.")

    print("\n📄 گزارش نهایی آماده است.")
    exit()

else:
    # ---- Option 1: New diet design ----
    clear()
    print("\n" + "=" * 60)
    print("🍽️  طراحی رژیم غذایی جدید")
    print("=" * 60)
    print("\nلطفاً اطلاعات زیر را برای طراحی رژیم جدید وارد کنید:\n")
while True:
    housing = input(f"9. محل نگهداری {pet_name}؟\n""1. فقط خانه\n""2. فقط خارج از خانه\n""3. هر دو\n""انتخاب شما: ")
    if housing in ["1", "2", "3"]: break
housing = ["indoor", "outdoor", "both"][int(housing) - 1]

clear()
while True:
    other_pet = input(f"10. حیوان خانگی دیگر در منزل؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if other_pet in ["1", "2"]: break

if other_pet == "1":
    clear()
    while True:
        other_species = input("10.1 گونه حیوان دیگر؟\n""1. سگ\n""2. گربه\n""انتخاب شما: ")
        if other_species in ["1", "2"]: break
    other_species = "dog" if other_species == "1" else "cat"

    clear()
    while True:
        food_access = input(f"10.2 آیا {other_species} به غذای {pet_name} دسترسی دارد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
        if food_access in ["1", "2"]: break

clear()
while True:
    uncontrolled_food = input(f"11. آیا {pet_name} دسترسی به منابع غذایی غیرقابل کنترل دارد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if uncontrolled_food in ["1", "2"]: break

if uncontrolled_food == "1":
    clear()
    print("11.1 منابع غذایی غیرقابل کنترل (چند انتخابی):\n"
          "1. غذای سفره خانواده\n"
          "2. غذای همسایه / دوستان / آشنایان\n"
          "3. کابینت و قسمت‌های دیگر خانه\n"
          "4. سطل زباله\n"
          "5. سایر")
    food_sources = input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")

    if "5" in food_sources:
        clear()
        other_food_source = input("11.1.5 سایر منابع: ")

clear()
while True:
    feeder = input(f"12. چه کسی به {pet_name} غذا می‌دهد؟\n""1. خودم\n""2. فرد دیگر\n""انتخاب شما: ")
    if feeder in ["1", "2"]: break

if feeder == "2":
    clear()
    while True:
        feeding_knowledge = input("12.1 آیا اطلاع کامل از نحوه و مقدار غذادهی او دارید؟\n""1. بله کامل\n""2. نسبتاً\n""3. خیر\n""انتخاب شما: ")
        if feeding_knowledge in ["1", "2", "3"]: break

clear()
vomit_count = int(input(f"13. طی ۳ ماه اخیر {pet_name} چند بار استفراغ یا پس‌زدن غذا داشته؟\n "))

if vomit_count > 0:
    clear()
    while True:
        vomit_time = input("13.1 زمان استفراغ معمولاً؟\n""1. کمتر از ۲ ساعت بعد از غذا\n""2. بیشتر از ۲ ساعت بعد از غذا\n""3. نامشخص\n""انتخاب شما: ")
        if vomit_time in ["1", "2", "3"]: break

    clear()
    print("13.2 محتوای استفراغ (چند انتخابی):\n"
          "1. غذای هضم‌نشده\n"
          "2. مایع زرد یا کف\n"
          "3. مواد خارجی (مو، آشغال و غیره)\n"
          "4. خون\n"
          "5. سایر")
    vomit_contents = input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")

    if "5" in vomit_contents:
        clear()
        vomit_other = input("13.2.5 سایر: ")

clear()
while True:
    stool_score = input(f"14. وضعیت مدفوع {pet_name} در ۳ هفته اخیر (۱ تا ۷):\n""۱. گلوله سفت و خشک\n""۴. نرم و شکل‌دار طبیعی\n""۷. کاملاً آبکی\n""اسکور: ")
    if stool_score.isdigit() and 1 <= int(stool_score) <= 7: break
stool_score = int(stool_score)

clear()


changes = None
weight_change_amount= None
weight_change= None
water_change= None
appetite_change_amount= None
while True:
    recent_change = input(f"16. طی ۳ ماه اخیر {pet_name} تغییری در اشتها، وزن یا میزان آب‌نوشیدن داشته؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if recent_change in ["1", "2"]: break

if recent_change == "1":
    clear()
    print("16.1 کدام موارد تغییر کرده؟ (چند انتخابی):\n""1. وزن\n""2. میزان آب\n""3. اشتها")
    changes = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]

    if "1" in changes:
        clear()
        while True:
            weight_change = input("16.2 تغییر وزن؟\n""1. کاهش\n""2. افزایش\n""انتخاب شما: ")
            if weight_change in ["1", "2"]: break
        weight_change_amount = float(input("مقدار تقریبی تغییر وزن (کیلوگرم): "))
        weight_change_duration = int(input("مدت زمان تغییر وزن (هفته): "))

    if "2" in changes:
        clear()
        while True:
            water_change = input("16.3 تغییر آب؟\n""1. کاهش\n""2. افزایش\n""انتخاب شما: ")
            if water_change in ["1", "2"]: break
        water_change_duration = int(input("مدت زمان تغییر آب (هفته): "))

    if "3" in changes:
        clear()
        while True:
            appetite_change = input("16.4 تغییر اشتها؟\n""1. افزایش\n""2. کاهش\n""انتخاب شما: ")
            if appetite_change in ["1", "2"]: break
        appetite_change_amount = int(input("مقدار تغییر اشتها (درصد): "))

clear()
while True:
    pica = input(f"17. آیا {pet_name} میل به خوردن اشیاء غیرخوراکی دارد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
    if pica in ["1", "2"]: break

if pica == "1":
    clear()
    print("17.1 اشیاء غیرخوراکی (چند انتخابی):\n"
          "1. خاک، ماسه، خاک رس، مالچ\n"
          "2. سنگ و قلوه‌سنگ\n"
          "3. مدفوع حیوانات (خودش یا دیگران)\n"
          "4. خاک گربه (litter)\n"
          "5. علف، برگ، گیاهان\n"
          "6. مو (خودش یا انسان)\n"
          "7. جوراب، لباس، حوله، پتو، فرش و الیاف\n"
          "8. نخ، کاموا، ریسمان، روبان، کش\n"
          "9. کیسه پلاستیکی، سلفون، قطعات اسباب‌بازی، فوم\n"
          "10. کاغذ، مقوا، کتاب\n"
          "11. ته سیگار و خاکستر\n"
          "12. صابون، شامپو، خمیر دندان\n"
          "13. اشیاء فلزی (سکه، میخ، فویل)\n"
          "14. سیم برق، بتن، گچ، دیوار\n"
          "15. برف یا یخ\n"
          "16. سایر")
    pica_items = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]

    if "16" in pica_items:
        clear()
        pica_other = input("17.1.16 سایر اشیاء غیرخوراکی: ")

clear()
while True:
    dental = input(f"18. آیا {pet_name} مشکلات دندانی یا مشکل در جویدن غذای سفت دارد؟\n""1. بله\n""2. خیر\n""3. نمی‌دانم\n""انتخاب شما: ")
    if dental in ["1", "2", "3"]: break

clear()

print("\n📋 غذاهای انتخاب‌شده شما:")
for i, food in enumerate(selected_foods, 1):
    print(f"  {i}. {food}")

while True:
    food_choice = input("\nآیا می‌خواهید به این غذاها اضافه کنید یا غذاهای جدید انتخاب کنید؟\n"
                        "1. به غذاهای قبلی اضافه شود\n"
                        "2. غذاهای جدید انتخاب شوند\n"
                        "انتخاب شما: ")
    if food_choice in ["1", "2"]:
        break
add_to_existing = food_choice == "1"

# ----- Collect new foods (only names, no amounts) -----
new_foods = []

# ---------- Dry food ----------
dry_food_new = input(f"19. آیا {pet_name} غذای خشک مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
if dry_food_new == "1":
    dry_file = food_central / "Dry foods.xlsx"
    foods = pd.read_excel(dry_file)
    categories = foods["category"].dropna().unique()

    clear()
    print("19.1 دسته ماده غذایی خشک را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("19.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام ماده خشک از دسته «{cat}»: ")
            new_foods.append(f"Dry Food - {food_name}")
            clear()
            continue

        clear()
        print(f"19.1 مواد خشک از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام ماده خشک از دسته «{cat}»: ")
                new_foods.append(f"Dry Food - {other_food}")
                continue
            new_foods.append(f"Dry Food - {cat_foods[int(choice) - 1]}")
            clear()

# ---------- Wet food ----------
wet_food_new = input(f"20. آیا {pet_name} غذای تر مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
if wet_food_new == "1":
    wet_file = food_central / "Wet foods.xlsx"
    foods = pd.read_excel(wet_file)
    categories = foods["category"].dropna().unique()

    clear()
    print("20.1 دسته غذای تر را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("20.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام غذای تر از دسته «{cat}»: ")
            new_foods.append(f"Wet Food - {food_name}")
            clear()
            continue

        clear()
        print(f"20.1 مواد تر از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام غذای تر از دسته «{cat}»: ")
                new_foods.append(f"Wet Food - {other_food}")
                continue
            new_foods.append(f"Wet Food - {cat_foods[int(choice) - 1]}")
            clear()

# ---------- Treats ----------
treats_new = input(f"21. آیا {pet_name} تشویقی می‌خورد؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
if treats_new == "1":
    treats_file = food_central / "Treats.xlsx"
    foods = pd.read_excel(treats_file)
    categories = foods["category"].dropna().unique()

    clear()
    print("21.1 دسته تشویقی را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("21.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام تشویقی از دسته «{cat}»: ")
            new_foods.append(f"Treats - {food_name}")
            clear()
            continue

        clear()
        print(f"21.1 تشویقی‌های دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام تشویقی از دسته «{cat}»: ")
                new_foods.append(f"Treats - {other_food}")
                continue
            new_foods.append(f"Treats - {cat_foods[int(choice) - 1]}")
            clear()

# ---------- Home food ----------
home_food_new = input(f"22. آیا {pet_name} غذای خانگی مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
if home_food_new == "1":
    homefood_file = food_central / "Home foods.xlsx"
    foods = pd.read_excel(homefood_file)
    categories = foods["category"].dropna().unique()

    clear()
    print("22.1 دسته غذای خانگی را انتخاب کنید (چند انتخابی):\n")
    for i, cat in enumerate(categories, 1): print(f"{i}. {cat}")
    print(f"{len(categories) + 1}. سایر")
    while True:
        cat_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
        if cat_choices and all(x.isdigit() and 1 <= int(x) <= len(categories) + 1 for x in cat_choices):
            break

    selected_categories = []
    for choice in cat_choices:
        if int(choice) == len(categories) + 1:
            clear()
            other_cat = input("22.1 سایر دسته: ")
            selected_categories.append(other_cat)
            continue
        selected_categories.append(categories[int(choice) - 1])

    for cat in selected_categories:
        if cat in categories:
            cat_foods = foods[foods["category"] == cat]["foods"].dropna().tolist()
        else:
            cat_foods = []

        if not cat_foods and cat not in categories:
            clear()
            food_name = input(f"نام غذای خانگی از دسته «{cat}»: ")
            new_foods.append(f"Home Food - {food_name}")
            clear()
            continue

        clear()
        print(f"22.1 مواد غذایی از دسته «{cat}» (چند انتخابی):\n")
        for i, food in enumerate(cat_foods, 1): print(f"{i}. {food}")
        print(f"{len(cat_foods) + 1}. سایر")

        while True:
            food_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
            if food_choices and all(x.isdigit() and 1 <= int(x) <= len(cat_foods) + 1 for x in food_choices):
                break

        for choice in food_choices:
            if int(choice) == len(cat_foods) + 1:
                clear()
                other_food = input(f"نام غذای خانگی از دسته «{cat}»: ")
                new_foods.append(f"Home Food - {other_food}")
                continue
            new_foods.append(f"Home Food - {cat_foods[int(choice) - 1]}")
            clear()

# ---------- Supplements ----------
supplements_new = input(f"23. آیا {pet_name} مکمل مصرف می‌کند؟\n""1. بله\n""2. خیر\n""انتخاب شما: ")
if supplements_new == "1":
    supp_file = food_central / "Supplements.xlsx"
    supp_df = pd.read_excel(supp_file)
    supp_list = supp_df["مکمل"].dropna().unique().tolist()
    clear()
    print("23.1 نوع مکمل (چند انتخابی):\n")
    for i, supp in enumerate(supp_list, 1): print(f"{i}. {supp}")
    print(f"{len(supp_list) + 1}. سایر")
    supp_choices = [x.strip() for x in input("شماره گزینه‌ها را با کاما وارد کنید: ").split(",")]
    for ch in supp_choices:
        if ch.isdigit():
            idx = int(ch)
            if 1 <= idx <= len(supp_list):
                new_foods.append(f"Supplement - {supp_list[idx - 1]}")
            elif idx == len(supp_list) + 1:
                clear()
                other = input("23.1 سایر مکمل: ")
                new_foods.append(f"Supplement - {other}")
    clear()

# ----- Combine with existing or replace -----
if add_to_existing:
    selected_foods.extend(new_foods)
else:
    selected_foods = new_foods

# For current_diet, we set zeros (no amounts)
# current_diet = [0] * len(selected_foods)

clear()
print("\n" + "=" * 60)
print("✅ لیست غذاهای جدید ثبت شد.")
print("📋 فهرست نهایی غذاها:")
for i, food in enumerate(selected_foods, 1):
    print(f"  {i}. {food}")
print("=" * 60)
print("\n🔚 پایان برنامه.")
print("\n🔄  طراحی رژیم غذایی بر اساس این اطلاعات انجام خواهد شد.")

# =================================================================
# ROBUST NEW DIET FORMULATION (Persian UI, with category % constraints)
# =================================================================
import numpy as np, pandas as pd
from scipy.optimize import linprog, lsq_linear

allfood_file = food_central / "All foods.xlsx"
df_all = pd.read_excel(allfood_file)
new_diet_food_database = {}
for _, row in df_all.iterrows():
    food_name = row["foods"]
    nutrients = row.drop(labels=["category", "foods"]).to_dict()
    for k, v in nutrients.items():
        try: nutrients[k] = float(v)
        except: nutrients[k] = 0.0
    new_diet_food_database[food_name] = {
        "category": row.get("category", ""),
        "nutrients": nutrients
    }

MAX_FOOD_GRAMS, TOLERANCE = 1000.0, 1e-7

CATEGORY_MAX = {
    "گوشت قرمز": 60, "مرغ و بوقلمون": 60, "امعا و احشاء": 60,
    "ماهی و دریایی": 60, "تخم مرغ": 60, "لبنیات": 60,
    "غلات": 60, "حبوبات": 60, "سبزیجات": 60,
    "میوه": 30, "روغن و چربی": 30, "دانه و مغز": 30,
}
DEFAULT_MAX = 60

def food_value(food, nutrient, db):
    if food not in db: return 0.0
    try:
        v = db[food]["nutrients"].get(nutrient, 0.0)
        return float(v) if not pd.isna(v) else 0.0
    except: return 0.0

def available_nutrients(foods, db, reqs):
    out = []
    for n in reqs.index:
        for f in foods:
            if f in db and n in db[f]["nutrients"]:
                out.append(n); break
    return out

def calculate_diet_totals(foods, amounts, db):
    totals = {}
    for f, g in zip(foods, amounts):
        if f not in db: continue
        for n, v in db[f]["nutrients"].items():
            try:
                val = float(v)
                if not pd.isna(val):
                    totals[n] = totals.get(n, 0.0) + val * float(g) / 100.0
            except: continue
    return totals

def check_final_diet(totals, reqs):
    problems = []
    for n, r in reqs.iterrows():
        actual = totals.get(n, 0.0)
        mr = r.get("MR", np.nan)
        if pd.notna(mr) and actual < float(mr) - TOLERANCE:
            problems.append({"nutrient": n, "type": "MR", "required": float(mr), "actual": actual})
        sul = r.get("SUL", np.nan)
        if pd.notna(sul) and actual > float(sul) + TOLERANCE:
            problems.append({"nutrient": n, "type": "SUL", "required": float(sul), "actual": actual})
    return problems

def print_diet_result(result, db, ME):
    print("\n" + "="*70 + "\nرژیم نهایی\n" + "="*70)
    print(f"روش بهینه‌سازی: {result['method']}\nانرژی هدف: {float(ME):.2f} کیلوکالری در روز\n\nمقادیر غذاها:")
    for f, g in zip(result["foods"], result["amounts"]):
        print(f"  {f}: {float(g):.2f} گرم در روز")
    actual = sum(food_value(f, "kcal/100gr", db)*float(g)/100.0 for f,g in zip(result["foods"], result["amounts"]))
    print(f"\nانرژی واقعی: {actual:.2f} کیلوکالری در روز")
    problems = result.get("problems", [])
    if problems:
        print("\n" + "-"*70 + "\nمحدودیت‌های تغذیه‌ای برآورده نشدند\n" + "-"*70)
        for p in problems:
            if p["type"] == "MR": print(f"کمبود MR — {p['nutrient']}: {p['actual']:.4f} < MR {p['required']:.4f}")
            else: print(f"تجاوز از SUL — {p['nutrient']}: {p['actual']:.4f} > SUL {p['required']:.4f}")
    else:
        print("\nهمه محدودیت‌های MR و SUL برآورده شده‌اند.")
    print("="*70)

# ---------- LP with category percentage constraints ----------
def lp_with_percent_constraints(foods, db, reqs, ME, min_pct, category_max):
    nf = len(foods)
    nutrients = available_nutrients(foods, db, reqs)
    A_ub, b_ub = [], []
    # MR/SUL
    for n in nutrients:
        coeff = np.array([food_value(f, n, db)/100.0 for f in foods])
        req = reqs.loc[n]
        mr = req.get("MR", np.nan)
        sul = req.get("SUL", np.nan)
        if pd.notna(mr):
            row = np.zeros(nf); row[:] = -coeff; A_ub.append(row); b_ub.append(-float(mr))
        if pd.notna(sul):
            row = np.zeros(nf); row[:] = coeff; A_ub.append(row); b_ub.append(float(sul))
    # Energy
    energy = np.array([food_value(f, "kcal/100gr", db)/100.0 for f in foods])
    A_eq = [energy]; b_eq = [float(ME)]
    # Percentage bounds
    for i, f in enumerate(foods):
        cat = db[f]["category"]
        max_pct = category_max.get(cat, DEFAULT_MAX) / 100.0
        min_pct_frac = min_pct / 100.0
        # min: -x_i + min_pct*sum(x) <= 0
        row_min = np.zeros(nf); row_min[i] = -1.0; row_min += min_pct_frac
        A_ub.append(row_min); b_ub.append(0.0)
        # max: x_i - max_pct*sum(x) <= 0
        row_max = np.zeros(nf); row_max[i] = 1.0; row_max -= max_pct
        A_ub.append(row_max); b_ub.append(0.0)
    bounds = [(0.0, MAX_FOOD_GRAMS) for _ in foods]
    try:
        res = linprog(
            np.zeros(nf),
            A_ub=np.array(A_ub),
            b_ub=np.array(b_ub),
            A_eq=np.array(A_eq),
            b_eq=np.array(b_eq),
            bounds=bounds,
            method="highs"
        )

        if res.success:
            return res.x
        else:
            print("LP FAILED")
            print("Status:", res.status)
            print("Message:", res.message)
            return None

    except Exception as e:
        print("LP EXCEPTION:", type(e).__name__, e)
        raise

# ---------- Weighted LSQ with category percentage penalty ----------
def lsq_with_percent_penalty(foods, db, reqs, ME, min_pct, category_max):
    nutrients = available_nutrients(foods, db, reqs)
    nf = len(foods)
    rows, targets, weights = [], [], []
    # Energy
    e = np.array([food_value(f, "kcal/100gr", db)/100.0 for f in foods])
    rows.append(e); targets.append(float(ME)); weights.append(1000.0 / max(float(ME), 1.0))
    # Nutrients
    for n in nutrients:
        coeff = np.array([food_value(f, n, db)/100.0 for f in foods])
        req = reqs.loc[n]
        ra, mr = req.get("RA", np.nan), req.get("MR", np.nan)
        target = float(ra) if pd.notna(ra) else (float(mr) if pd.notna(mr) else 0.0)
        if target == 0.0: continue
        rows.append(coeff); targets.append(target)
        w = 1.0 / max(abs(target), 1.0)
        if any(x in n for x in ["Protein", "Arginine", "Lysine", "Methionine", "Leucine", "Valine"]):
            w *= 5.0
        if n == "Total Fat (gr)": w = 10000.0
        elif n == "a-Linolenic Acid n-3 (gr)": w = 5000.0
        weights.append(w)
    A = np.array(rows, dtype=float); b = np.array(targets, dtype=float); w = np.array(weights, dtype=float)
    A_w = A * w[:, np.newaxis]; b_w = b * w
    try:
        res = lsq_linear(A_w, b_w, bounds=(np.zeros(nf), np.full(nf, MAX_FOOD_GRAMS)),
                         method="trf", lsmr_tol="auto", verbose=0)
        if res.x is None: return None
        amounts = np.maximum(res.x, 0.0)
        # Enforce min/max percentages by scaling
        total = amounts.sum()
        if total == 0: return amounts
        # enforce minimum
        min_g = (min_pct / 100.0) * total
        for i in range(nf):
            if amounts[i] < min_g:
                amounts[i] = min_g
        # rescale to keep total
        new_total = amounts.sum()
        if new_total > 0: amounts *= total / new_total
        # enforce maximum
        for i, f in enumerate(foods):
            cat = db[f]["category"]
            max_pct = category_max.get(cat, DEFAULT_MAX) / 100.0
            max_g = max_pct * amounts.sum()
            if amounts[i] > max_g:
                amounts[i] = max_g
        # re‑scale energy
        amounts = correct_energy(foods, amounts, db, ME)
        return amounts
    except: return None

def correct_energy(foods, amounts, db, ME):
    amounts = np.array(amounts, dtype=float)
    energy = sum(food_value(f, "kcal/100gr", db)*g/100.0 for f, g in zip(foods, amounts))
    if energy <= 0: return amounts
    factor = float(ME) / energy
    return np.minimum(np.maximum(amounts * factor, 0.0), MAX_FOOD_GRAMS)

def emergency_diet(foods, db, ME):
    nf = len(foods)
    densities = np.array([food_value(f, "kcal/100gr", db)/100.0 for f in foods])
    total = densities.sum()
    if total <= 0: return np.zeros(nf)
    amounts = (float(ME) / total) * (densities / total)
    return np.minimum(amounts, MAX_FOOD_GRAMS)

# ---------- Main formulator ----------
def formulate_new_diet(foods, db, reqs, ME, auto_balance=False):
    foods = list(dict.fromkeys(foods))
    foods = [f for f in foods if f in db]
    if not foods:
        return {"success": False, "message": "هیچ غذایی در پایگاه داده یافت نشد."}
    if not any(food_value(f, "kcal/100gr", db) > 0 for f in foods):
        return {"success": False, "message": "غذاهای انتخاب شده انرژی قابل‌استفاده‌ای ندارند."}

    # Stage 1: LP without % constraints
    print("\nمرحله ۱: LP بدون محدودیت درصد...")
    amounts = lp_with_percent_constraints(foods, db, reqs, ME, 0.0, {})
    if amounts is not None:
        totals = calculate_diet_totals(foods, amounts, db)
        problems = check_final_diet(totals, reqs)
        print("LP موفق شد.")
        return {"success": True, "amounts": amounts, "foods": foods, "method": "LP (بدون درصد)",
                "totals": totals, "problems": problems}
    print("LP بدون محدودیت درصد شکست خورد.")

    if auto_balance:
        print("\nتلاش برای رژیم متعادل با محدودیت درصد...")
        for min_pct in [10, 5, 2, 1, 0.5, 0.1]:
            print(f"  تلاش با حداقل {min_pct}% برای هر غذا...")
            amounts = lp_with_percent_constraints(foods, db, reqs, ME, min_pct, CATEGORY_MAX)
            if amounts is not None:
                totals = calculate_diet_totals(foods, amounts, db)
                problems = check_final_diet(totals, reqs)
                print(f"  LP متعادل با {min_pct}% موفق شد.")
                return {"success": True, "amounts": amounts, "foods": foods,
                        "method": f"LP متعادل ({min_pct}% حداقل)",
                        "totals": totals, "problems": problems}
        print("  LP متعادل حتی با 0.1% شکست خورد. افتادن به LSQ.")

    # Stage 2: LSQ with percentage enforcement
    print("\nمرحله ۲: کمترین مربعات با محدودیت درصد...")
    amounts = lsq_with_percent_penalty(foods, db, reqs, ME, 10.0, CATEGORY_MAX)  # enforce 10% min
    if amounts is not None:
        totals = calculate_diet_totals(foods, amounts, db)
        problems = check_final_diet(totals, reqs)
        print("LSQ با محدودیت درصد موفق شد.")
        return {"success": True, "amounts": amounts, "foods": foods, "method": "LSQ با درصد",
                "totals": totals, "problems": problems}
    print("LSQ با محدودیت درصد شکست خورد.")

    # Stage 3: Emergency
    print("\nمرحله ۳: رژیم اضطراری...")
    amounts = emergency_diet(foods, db, ME)
    totals = calculate_diet_totals(foods, amounts, db)
    problems = check_final_diet(totals, reqs)
    print("رژیم اضطراری تولید شد.")
    return {"success": True, "amounts": amounts, "foods": foods, "method": "اضطراری",
            "totals": totals, "problems": problems}

# =================================================================
# START
# =================================================================
print("\n" + "="*70 + "\nشروع فرمول‌سازی رژیم جدید\n" + "="*70)
optimization_foods = [f.split(" - ", 1)[1] if " - " in f else f for f in selected_foods]
optimization_foods = list(dict.fromkeys(optimization_foods))

print("\nLP بدون محدودیت درصد (تلاش اولیه) شکست خورد.")
auto_choice = input("آیا می‌خواهید موتور به‌طور خودکار یک رژیم متعادل از غذاهای انتخاب‌شده شما ایجاد کند؟\n"
                    "۱. بله، رژیم متعادل با محدودیت درصد دسته‌بندی ایجاد کن\n"
                    "۲. خیر، فقط بهترین رژیم ممکن را تولید کن (بدون محدودیت درصد)\n"
                    "انتخاب شما: ").strip()
auto_balance = (auto_choice == "۱")

result = formulate_new_diet(optimization_foods, new_diet_food_database, daily_requirements, ME, auto_balance)
if not result["success"]:
    print("\nخطا: " + result["message"])
else:
    print_diet_result(result, new_diet_food_database, ME)
    if result["problems"]:
        print("\nتوجه: برخی محدودیت‌ها برآورده نشدند. برای رفع آنها می‌توانید غذاهای پیشنهادی را اضافه کنید.")