import React, { Component } from 'react'
import { Row, Col } from 'antd'
import BarCharts from './BarCharts'
import PieCharts from './PieCharts'

class ActiveAmount extends Component{
  render(){
    return(
      <div>
        <Row >
          <Col span={15}>
            <BarCharts title={'活跃量趋势'}/>
          </Col>
          <Col span={8} className="change_pie_size">
            <PieCharts />
          </Col>
        </Row>

      </div>
    )
  }
}

export default ActiveAmount
