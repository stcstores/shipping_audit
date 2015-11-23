import matplotlib.pyplot as plt
import datetime

import lstools


class Shipping_Service():

    def __init__(self, name):
        self.name = name
        self.shipments = []

    def add_shipment(self, sent, recieved, order_id):
        self.shipments.append(Shipment(self.name, sent, recieved, order_id))

    def get_shipping_times(self):
        shipping_times = []
        for shipment in self.shipments:
            shipping_times.append(shipment.shipment_time())
        return shipping_times

    def __getitem__(self, key):
        return self.shipments[key]

    def __iter__(self):
        return self.shipments

    def make_chart(self):
        fig = plt.figure()
        fig.suptitle(self.name, fontsize=20)
        plt.autoscale()
        shipping_times_for_days = []
        shipping_times = self.get_shipping_times()
        for i in range(1000):
            shipping_times_for_days.append(
                shipping_times.count(i))

        shipping_times_for_days = Shipping_Data.remove_trailing_zero_from_list(
            shipping_times_for_days)

        day_counts = list(range(len(shipping_times_for_days)))
        plt.xticks(
            list(range(1, len(shipping_times_for_days))), fontsize=20)
        plt.yticks(
            list(range(1, max(shipping_times_for_days) + 1)), fontsize=20)
        ax = plt.axes()
        ax.yaxis.grid(color='black')
        ax.xaxis.grid(False)
        plt.axis([0, max(day_counts) + 1, 0, max(shipping_times_for_days) + 1])
        plt.bar(day_counts, shipping_times_for_days, align="center")
        plt.xlabel("Days in transit")
        fig.savefig('charts/' + self.name + '.png', bbox_inches='tight')


class Shipment():

    def __init__(self, service, sent, recieved, order_id):
        self.service = service
        self.sent = sent
        self.recieved = recieved
        self.order_number = order_id

    def daterange(self, start_date, end_date):
        for n in range(int((end_date - start_date).days)):
            yield start_date + datetime.timedelta(n)

    def shipment_time(self):
        days = 0
        start_date = self.sent
        end_date = self.recieved
        for single_date in self.daterange(start_date, end_date):
            if single_date.weekday() != 6:
                days += 1
        return days


class Shipping_Data():

    def __init__(self):
        db = lstools.DatabaseConnection(host='mysql.stcadmin.stcstores.co.uk',
                                        user='seatontrading',
                                        passwd='Cosworth1',
                                        database='seatontrading')
        results = db.query("SELECT * FROM shipping_audit")
        shipping_services = {}
        for result in results:
            sent = result[1]
            recieved = result[2]
            service = result[3]
            order_id = result[4]
            if service not in shipping_services:
                shipping_services[service] = Shipping_Service(service)
            shipping_services[service].add_shipment(sent, recieved, order_id)
        self.shipping_services = shipping_services

    def make_charts(self):
        for service in self.shipping_services:
            self.shipping_services[service].make_chart()

    def remove_trailing_zero_from_list(array):
        if len(array) < 2:
            return array
        elif array[-1] != 0:
            return array
        elif array.count(0) == len(array):
            return array
        else:
            i = len(array) - 1
            while array[i] == 0:
                array.pop(i)
                i -= 1
        return array


if __name__ == "__main__":
    plt.style.use('ggplot')
    shipping_data = Shipping_Data()
    shipping_data.make_charts()
