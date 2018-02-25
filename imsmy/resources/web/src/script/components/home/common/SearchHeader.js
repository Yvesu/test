import React, { Component } from 'react'
import HeaderSearch from 'ant-design-pro/lib/HeaderSearch'

class SearchHeader extends Component{
  render(){
    return(
      <div>
        <HeaderSearch
          placeholder="站内搜索"
          dataSource={['搜索提示一', '搜索提示二', '搜索提示三']}
          onSearch={(value) => {
            console.log('input', value); // eslint-disable-line
          }}
          onPressEnter={(value) => {
            console.log('enter', value); // eslint-disable-line
          }}
        />
      </div>
    )
  }
}
export default SearchHeader
