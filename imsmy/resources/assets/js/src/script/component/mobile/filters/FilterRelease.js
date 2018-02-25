import React, { Component } from 'react'
import {Layout} from 'antd'
import ReleaseSteps from './release/ReleaseSteps'

class FilterRelease extends Component {
  render(){
    return(
      <Layout className="release_filter_box">
        <h3>发布滤镜</h3>
        <ReleaseSteps />
      </Layout>
    )
  }
}

export default FilterRelease
